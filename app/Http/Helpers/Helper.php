<?php

namespace App\Http\Helpers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\App;
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

        // Report to Bugsnag on staging/production
        if (!App::environment('local') && $errorObject) {
            Bugsnag::notifyException($errorObject, function ($report) use ($message, $location, $reference) {
                $report->setMetaData([
                    'context' => [
                        'message' => $message,
                        'location' => is_array($location) ? implode('@', $location) : $location,
                        'reference' => $reference,
                    ],
                ]);
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

        // Report to Bugsnag on staging/production
        if (!App::environment('local')) {
            Bugsnag::notifyException(
                new Exception($message),
                function ($report) use ($message, $location, $reference) {
                    $report->setSeverity('warning');
                    $report->setMetaData([
                        'context' => [
                            'message' => $message,
                            'location' => is_array($location) ? implode('@', $location) : $location,
                            'reference' => $reference,
                        ],
                    ]);
                }
            );
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
