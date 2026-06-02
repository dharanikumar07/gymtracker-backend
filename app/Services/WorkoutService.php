<?php

namespace App\Services;

use App\Http\Resources\SlotResource;
use App\Models\PhysicalActivitySlot;
use App\Data\PhysicalActivityData\PhysicalActivityFactory;
use Exception;

class WorkoutService
{
    public function saveWorkoutSlots($userUuid, array $slots)
    {
        $savedSlots = [];
        foreach ($slots as $slotData) {
            $targetMuscles = $slotData['target_muscles'] ?? ($slotData['meta_data']['target_muscles'] ?? []);

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
                    'meta_data' => [
                        'target_muscles' => $targetMuscles,
                    ],
                ]
            );

            $savedSlots[] = $slot;
        }

        return $savedSlots;
    }

    public function getWorkoutSlotByDay($planUuid, $userUuid, $day): array
    {
        $slots = PhysicalActivitySlot::with('plan')
            ->where('user_uuid', $userUuid)
            ->where('plan_uuid', $planUuid)
            ->where('day', $day)
            ->orderBy('exercise_order')
            ->get();

        throw_if($slots->isEmpty(), new Exception("No workout slots found for this day"));

        $slotsTemplate = [];

        foreach ($slots as $slot) {
            $slotsTemplate[] = [
                'slot_uuid' => $slot->uuid,
                'plan_uuid' => $slot->plan_uuid,
                'exercise_name' => $slot->exercise_name,
                'exercise_order' => $slot->exercise_order,
                'day' => $slot->day,
                'type' => 'template',
                'metrics_type' => $slot->metrics_type,
                'metrics_data' => $slot->metrics_data,
                'meta_data' => $slot->meta_data,
            ];
        }

        return [
            'slots' => $slotsTemplate
        ];
    }

    public function getPendingTemplateWorkouts($logs, $planUuid, $userUuid, $day): array
    {
        $templateSlots = $this->getWorkoutSlotByDay($planUuid, $userUuid, $day);

        $loggedSlotUuids = collect($logs)
            ->pluck('slot_uuid')
            ->filter()
            ->toArray();

        $pendingSlots = collect($templateSlots['slots'])->filter(function ($slot) use ($loggedSlotUuids) {
            return !in_array($slot['slot_uuid'], $loggedSlotUuids);
        });

        return $pendingSlots->values()->toArray();
    }

    public function getDefaultMetricsDataByType(string $metricsType): array
    {
        return match ($metricsType) {
            'strength' => [
                'sets' => 3,
                'reps' => 15,
                'rest' => 30,
            ],
            'timed_sets' => [
                'sets' => 3,
                'duration' => 40,
                'duration_unit' => 'seconds',
                'rest' => 20,
            ],
            'endurance' => [
                'duration' => 60,
                'duration_unit' => 'seconds',
            ],
            'rest' => [],
            default => [],
        };
    }

    public function getMetricsDefaultUnitsMetrics($type): array
    {
        $physicalActivityClass = (new PhysicalActivityFactory($type))->getPhysicalActivityClass();

        return [
            'units' => $physicalActivityClass->getAvailableUnitTypes(),
            'metrics_types' => $physicalActivityClass->getAvailableMetricTypes(),
        ];
    }
}
