<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DietRoutineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metaData = $this->meta_data ?? [];

        return [
            'mon' => MealPlanResource::collection(
                $this->meals->where('day', 'mon')->values()
            ),
            'tue' => MealPlanResource::collection(
                $this->meals->where('day', 'tue')->values()
            ),
            'wed' => MealPlanResource::collection(
                $this->meals->where('day', 'wed')->values()
            ),
            'thu' => MealPlanResource::collection(
                $this->meals->where('day', 'thu')->values()
            ),
            'fri' => MealPlanResource::collection(
                $this->meals->where('day', 'fri')->values()
            ),
            'sat' => MealPlanResource::collection(
                $this->meals->where('day', 'sat')->values()
            ),
            'sun' => MealPlanResource::collection(
                $this->meals->where('day', 'sun')->values()
            ),
            'total_targets' => [
                'calories' => $metaData['target_calories'] ?? null,
                'protein' => $metaData['target_protein'] ?? null,
                'carbs' => $metaData['target_carbs'] ?? null,
                'fats' => $metaData['target_fats'] ?? null,
            ],
        ];
    }
}
