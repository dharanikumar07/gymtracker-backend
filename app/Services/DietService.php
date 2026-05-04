<?php

namespace App\Services;

class DietService
{
    public static function calculateTotals(array $foodData): array
    {
        $totals = ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fats' => 0];

        foreach ($foodData as $food) {
            $totals['calories'] += $food['calories'] ?? 0;
            $totals['protein'] += $food['protein'] ?? 0;
            $totals['carbs'] += $food['carbs'] ?? 0;
            $totals['fats'] += $food['fats'] ?? 0;
        }

        return [
            'calories' => round($totals['calories'], 2),
            'protein' => round($totals['protein'], 2),
            'carbs' => round($totals['carbs'], 2),
            'fats' => round($totals['fats'], 2),
        ];
    }
}
