<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'user_uuid' => $this->user_uuid,
            'plan_uuid' => $this->plan_uuid,
            'exercise_name' => $this->exercise_name,
            'exercise_order' => $this->exercise_order,
            'day' => $this->day,
            'metrics_type' => $this->metrics_type,
            'metrics_data' => $this->metrics_data ?? [],
            'meta_data' => $this->meta_data ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
