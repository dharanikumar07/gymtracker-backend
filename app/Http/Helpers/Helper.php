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

    public static function getAvailableDietWeightedUnits()
    {
        return [
            ['value' => 'g', 'label' => 'Grams'],
            ['value' => 'kg', 'label' => 'Kilograms'],
            ['value' => 'lbs', 'label' => 'Pounds'],
            ['value' => 'oz', 'label' => 'Ounces'],
            ['value' => 'ml', 'label' => 'Milliliters'],
            ['value' => 'l', 'label' => 'Liters'],
            ['value' => 'cup', 'label' => 'Cups'],
            ['value' => 'pcs', 'label' => 'Pieces'],
            ['value' => 'tbsp', 'label' => 'Tablespoons'],
            ['value' => 'tsp', 'label' => 'Teaspoons'],
            ['value' => 'slice', 'label' => 'Slices'],
            ['value' => 'bowl', 'label' => 'Bowls'],
            ['value' => 'plate', 'label' => 'Plates'],
            ['value' => 'quantity', 'label' => 'Quantity'],
        ];
    }
}
