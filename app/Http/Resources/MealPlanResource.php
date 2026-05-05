<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'meal_name' => $this->meal_name,
            'day' => $this->day,
            'time_period' => $this->time_period,
            'food_data' => $this->food_data,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbs' => $this->carbs,
            'fats' => $this->fats,
            'nutrition_data' => $this->nutrition_data,
        ];
    }
}
