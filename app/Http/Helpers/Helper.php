<?php

namespace App\Http\Helpers;

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

        Log::error([$message => $data]);
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

        Log::warning([$message => $data]);
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
