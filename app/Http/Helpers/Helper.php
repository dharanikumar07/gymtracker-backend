<?php

namespace App\Http\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class Helper
{
    public static function logError($message, $location, $errorObject = null, $reference = [])
    {
        $data = [
            'location' => is_array($location) ? implode('@', $location) : $location,
        ];

        if ($errorObject) {
            $data['message'] = $errorObject->getMessage();
            $data['trace'] = $errorObject->getTraceAsString();
        }

        if ($reference) {
            $data['reference'] = $reference;
        }

        // Always log locally
        Log::error([$message => $data]);

        // Report to Sentry on staging/production
        if (!App::environment('local') && $errorObject) {
            \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($message, $location, $reference, $errorObject) {
                $userUuid = self::getCurrentUserUuid();

                if ($userUuid) {
                    $scope->setUser(['id' => $userUuid]);
                }

                $scope->setContext('error_context', [
                    'message' => $message,
                    'location' => is_array($location) ? implode('@', $location) : $location,
                    'reference' => $reference,
                    'user_uuid' => $userUuid,
                ]);

                \Sentry\captureException($errorObject);
            });
        }
    }

    public static function logWarning($message, $location, $reference = [])
    {
        $data = [
            'location' => is_array($location) ? implode('@', $location) : $location,
        ];

        if ($reference) {
            $data['reference'] = $reference;
        }

        $data['message'] = $message;
        $data['location'] = $location ?? 'unknown';

        // Always log locally
        Log::warning([$message => $data]);

        // Report to Sentry on staging/production
        if (!App::environment('local')) {
            \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($message, $location, $reference) {
                $userUuid = self::getCurrentUserUuid();

                if ($userUuid) {
                    $scope->setUser(['id' => $userUuid]);
                }

                $scope->setLevel(\Sentry\Severity::warning());
                $scope->setContext('warning_context', [
                    'message' => $message,
                    'location' => is_array($location) ? implode('@', $location) : $location,
                    'reference' => $reference,
                    'user_uuid' => $userUuid,
                ]);

                \Sentry\captureMessage($message);
            });
        }
    }

    private static function getCurrentUserUuid(): ?string
    {
        try {
            return Auth::user()?->uuid;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function slugifyCategory($value)
    {
        return str_replace(' ', '_', trim($value));
    }

    public static function deslugifyCategory($value)
    {
        return str_replace('_', ' ', $value);
    }
}
