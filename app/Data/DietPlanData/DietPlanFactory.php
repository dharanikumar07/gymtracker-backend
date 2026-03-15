<?php

namespace App\Data\DietPlanData;

class DietPlanFactory
{
    public static function create(string $goal, string $dietType): DietPlanInterface
    {
        $goal = strtolower($goal);
        
        if (str_contains($goal, 'gain') || str_contains($goal, 'bulk')) {
            return new WeightGainPlan($dietType);
        }
        
        if (str_contains($goal, 'loss') || str_contains($goal, 'cut')) {
            return new WeightLossPlan($dietType);
        }
        
        return new MaintenancePlan($dietType);
    }
}
