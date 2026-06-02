<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use App\Http\Helpers\Helper;

class OnboardingService
{
    public function createWeeklyRoutineData(
        string $physicalActivityType,
        array $physicalActivityData,
        array $planData,
        string $userUuid
    ): ?bool {
        try {
            $plan = Plan::create([
                'user_uuid' => $userUuid,
                'name' => $planData['name'],
                'type' => Plan::PHYSICAL_ACTIVITY_TYPE,
                'meta_data' => [
                    'physical_activity_type' => $physicalActivityType,
                    'units' => $physicalActivityData['units'] ?? null,
                    'metrics_types' => $physicalActivityData['metrics_types'] ?? null,
                ],
                'start_date' => $planData['start_date'] ?? null,
                'end_date' => $planData['end_date'] ?? null,
                'is_active' => $planData['is_active'] ?? true,
            ]);

            $scheduleKey = $physicalActivityData['physical_activity_type'];
            $scheduleData = $physicalActivityData[$scheduleKey] ?? [];

            $exerciseOrder = 1;
            
            foreach ($scheduleData as $dayName => $dayData) {
                $dayLower = strtolower($dayName);
                $workouts = $dayData['workouts'] ?? [];

                foreach ($workouts as $workout) {
                    PhysicalActivitySlot::create([
                        'user_uuid' => $userUuid,
                        'plan_uuid' => $plan->uuid,
                        'exercise_name' => $workout['name'] ?? '',
                        'exercise_order' => $exerciseOrder,
                        'day' => $dayLower,
                        'metrics_type' => $workout['metrics']['type'] ?? null,
                        'metrics_data' => $workout['metrics']['data'] ?? null,
                        'meta_data' => [
                            'target_muscles' => $workout['target_muscles'] ?? [],
                        ],
                    ]);

                    $exerciseOrder++;
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
