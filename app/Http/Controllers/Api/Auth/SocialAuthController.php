<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuthTokenTrait;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use AuthTokenTrait;

    /**
     * Redirect the user to the social provider authentication page.
     */
    public function redirectToProvider($provider)
    {
        try {
            return Response::json([
                'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
            ]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Invalid provider'], 400);
        }
    }

    /**
     * Handle the callback from the social provider.
     */
    public function handleProviderCallback(Request $request)
    {
        try {
            $code = $request->input('code', '');
            $provider = $request->input('provider', '');

            info(config('services.google.redirect'));

            $tokenResponse = Http::asForm()->post(
                'https://oauth2.googleapis.com/token',
                [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'redirect_uri' => config('services.google.redirect'),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ]
            );

            $data = $tokenResponse->json();

            if (!isset($data['access_token'])) {
                Helper::logError('Google token exchange failed', $data);

                return response()->json([
                    'error' => 'Google authentication failed',
                    'details' => $data
                ], 401);
            }
            $accessToken = $tokenResponse['access_token'];

            $googleUser = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo')
                ->json();

            $email = $googleUser['email'] ?? null;
            $name = $googleUser['name'] ?? $googleUser['nickname'] ?? null;
            $providerId = $googleUser['id'] ?? null;


            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'is_onboarding_completed' => false,
                    'provider_name' => $provider,
                    'provider_id' => $providerId
                ]);

                event(new Registered($user));
            } else {
                $user->update([
                    'provider_name' => $provider,
                    'provider_id' => $googleUser['id'] ?? null,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }

            return $this->issueTokens($user);
        } catch (\Exception $e) {
            Helper::logError('Social login failed', [__CLASS__, __FUNCTION__], $e);
            return Response::json(['error' => 'Authentication failed'], 401);
        }
    }
}
