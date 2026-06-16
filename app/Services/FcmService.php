<?php

namespace App\Services;

use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $projectId;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');
    }

    /**
     * Send FCM notification to multiple device tokens.
     *
     * @param array  $tokens  List of FCM device tokens
     * @param string $title   Notification title
     * @param string $body    Notification body
     * @param array  $data    Optional data payload
     * @return array ['success' => int, 'failure' => int, 'errors' => array]
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('FCM: Failed to obtain access token');
            return ['success' => 0, 'failure' => count($tokens), 'errors' => ['Failed to obtain access token']];
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $results = ['success' => 0, 'failure' => 0, 'errors' => []];

        foreach ($tokens as $token) {
            try {
                // Data-only message — no 'notification' key.
                // This gives the client full control over when and how
                // to display the notification (foreground + background),
                // preventing FCM's auto-display which causes duplicates.
                $message = [
                    'message' => [
                        'token' => $token,
                        'data' => array_merge($data, [
                            'title' => $title,
                            'body' => $body,
                        ]),
                    ],
                ];

                $response = Http::withToken($accessToken)
                    ->post($url, $message);

                if ($response->successful()) {
                    $results['success']++;
                } else {
                    $results['failure']++;
                    $results['errors'][] = "Token: {$token}, Error: " . $response->body();

                    // Deactivate tokens that FCM says are no longer valid
                    if ($response->status() === 404 || str_contains($response->body(), 'UNREGISTERED')) {
                        UserDeviceToken::where('fcm_token', $token)->update(['is_active' => false]);
                        Log::info('FCM: Deactivated stale token', ['token' => substr($token, 0, 20) . '...']);
                    } else {
                        Log::warning('FCM: Failed to send to token', [
                            'token' => substr($token, 0, 20) . '...',
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $results['failure']++;
                $results['errors'][] = "Token: {$token}, Exception: " . $e->getMessage();
                Log::error('FCM: Exception sending notification', ['error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    /**
     * Get an OAuth2 access token from the service account credentials.
     */
    protected function getAccessToken(): ?string
    {
        try {
            $credentials = $this->loadCredentials();

            if (!$credentials) {
                Log::error('FCM: No valid service account credentials found');
                return null;
            }

            // Build JWT for Google OAuth2
            $now = time();
            $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = base64url_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signature = '';
            $privateKey = openssl_pkey_get_private($credentials['private_key']);

            if (!$privateKey) {
                Log::error('FCM: Failed to parse private key', ['openssl_error' => openssl_error_string()]);
                return null;
            }

            if (!openssl_sign("{$header}.{$claim}", $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error('FCM: Failed to sign JWT', ['openssl_error' => openssl_error_string()]);
                return null;
            }

            $signature = base64url_encode($signature);

            $jwt = "{$header}.{$claim}.{$signature}";

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FCM: Failed to get access token', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('FCM: Error obtaining access token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Load service account credentials from env JSON or file path.
     */
    protected function loadCredentials(): ?array
    {
        // Prefer inline JSON from env (for Render / cloud hosting)
        $json = config('services.fcm.credentials_json');

        if ($json) {
            $credentials = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($credentials['client_email'])) {
                return $credentials;
            }

            Log::error('FCM: FCM_CREDENTIALS_JSON env var is set but contains invalid JSON');
            return null;
        }

        // Fall back to file path (local dev)
        $credentialsFile = base_path(config('services.fcm.credentials_path'));

        if (!file_exists($credentialsFile)) {
            Log::error("FCM: Service account file not found at {$credentialsFile}");
            return null;
        }

        return json_decode(file_get_contents($credentialsFile), true);
    }

    /**
     * Get notification content for a given module.
     */
    public static function getNotificationContent(string $module): array
    {
        return match ($module) {
            'workout' => [
                'title' => 'Workout Reminder',
                'body' => 'You have not logged your workout today. Update your progress now.',
            ],
            'expense' => [
                'title' => 'Expense Reminder',
                'body' => 'You have not added your expense log today. Track it now.',
            ],
            default => [
                'title' => 'Reminder',
                'body' => 'You have a pending task to complete today.',
            ],
        };
    }
}

/**
 * Base64 URL-safe encode (no padding).
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
