<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DietLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'meal_plan_uuid' => $this->meal_plan_uuid,
            'meal_name' => $this->whenLoaded('mealPlan', $this->mealPlan->meal_name),
            'day' => $this->day,
            'type' => $this->type,
            'status' => $this->status,
            'reason' => $this->reason,
            'food_data' => $this->food_data,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbs' => $this->carbs,
            'fats' => $this->fats,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
