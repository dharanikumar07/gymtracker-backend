<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slot_uuid' => $this->slot_uuid,
            'exercise_name' => $this->slot?->exercise_name ?? $this->exercise_name ?? null,
            'metrics_type' => $this->slot?->metrics_type ?? $this->metrics_type ?? null,
            'metrics_data' => $this->metrics_data ?? [],
            'activity_date' => $this->activity_date,
            'day' => $this->day,
            'status' => $this->status,
            'type' => $this->type,
            'reason' => $this->reason,
            'meta_data' => $this->slot?->meta_data ?? [],
            'created_at' => $this->created_at,
        ];
    }
}
