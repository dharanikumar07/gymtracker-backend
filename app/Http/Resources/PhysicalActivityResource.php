<?php

namespace App\Http\Resources;

use App\Data\PhysicalActivityData\PhysicalActivityFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhysicalActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $physicalActivityType = $this->resource['plan']->meta_data['physical_activity_type'] ?? null;
        $activityClass = (new PhysicalActivityFactory($physicalActivityType))->getPhysicalActivityClass();

        return [
            'plan' => [
                'uuid' => $this->resource['plan']->uuid,
                'name' => $this->resource['plan']->name,
                'type' => $this->resource['plan']->type,
                'meta_data' => $this->resource['plan']->meta_data ?? [],
                'start_date' => $this->resource['plan']->start_date,
                'end_date' => $this->resource['plan']->end_date,
                'is_active' => $this->resource['plan']->is_active,
            ],
            'slots' => $this->resource['slots']->map(function ($slot) {
                return [
                    'uuid' => $slot->uuid,
                    'user_uuid' => $slot->user_uuid,
                    'plan_uuid' => $slot->plan_uuid,
                    'exercise_name' => $slot->exercise_name,
                    'exercise_order' => $slot->exercise_order,
                    'day' => $slot->day,
                    'metrics_type' => $slot->metrics_type,
                    'metrics_data' => $slot->metrics_data ?? [],
                    'meta_data' => $slot->meta_data ?? [],
                ];
            }),
            'physical_activity_type' => $physicalActivityType,
            'units' => $activityClass->getAvailableUnitTypes(),
            'metrics_types' => $activityClass->getAvailableMetricTypes(),
        ];
    }
}
