<?php

namespace App\Services;

use App\Http\Resources\SlotResource;
use App\Models\PhysicalActivitySlot;
use App\Data\PhysicalActivityData\PhysicalActivityFactory;

class WorkoutService
{
    public function saveWorkoutSlots($userUuid, array $slots)
    {
        $savedSlots = [];
        foreach ($slots as $slotData) {
            $slot = PhysicalActivitySlot::updateOrCreate(
                [
                    'uuid' => $slotData['uuid'] ?? null,
                    'user_uuid' => $userUuid,
                ],
                [
                    'plan_uuid' => $slotData['plan_uuid'],
                    'exercise_name' => $slotData['exercise_name'],
                    'exercise_order' => $slotData['exercise_order'] ?? 1,
                    'day' => $slotData['day'],
                    'metrics_type' => $slotData['metrics_type'] ?? null,
                    'metrics_data' => $slotData['metrics_data'] ?? [],
                    'meta_data' => $slotData['meta_data'] ?? [],
                ]
            );

            $savedSlots[] = $slot;
        }
        
        return $savedSlots;
    }
}
