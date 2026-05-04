<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\MealPlan;
use App\Data\DietPlanData\DietPlanFactory;
use App\Data\DietPlanData\FoodLibrary;
use App\Http\Helpers\Helper;
use App\Services\DietService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DietController extends Controller
{
    /**
     * Get a diet routine.
     */
    public function getDietRoutine(Request $request)
    {
        try {
            $user = Auth::user();
            $planUuid = $request->query('plan_uuid');

            throw_if(!$planUuid, new Exception('Plan UUID is required'));

            $plan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('uuid', $planUuid)
                ->first();

            if (!$plan) {
                return Response::json([
                    'message' => 'Diet plan not found',
                    'available_plans' => Plan::where('user_uuid', $user->uuid)
                        ->where('type', 'diet')
                        ->select('uuid as plan_uuid', 'name')
                        ->get()
                ], HttpFoundationResponse::HTTP_NOT_FOUND);
            }

            $meals = MealPlan::where('plan_uuid', $plan->uuid)->get();

            if ($meals->isEmpty()) {
                $metaData = $plan->meta_data ?? [];
                $goal = $metaData['goal'] ?? 'maintenance';
                $dietPreference = $metaData['diet_preference'] ?? 'veg';
                $weight = (float)($user->user_fitness_data['weight'] ?? 70);

                DB::beginTransaction();

                $generator = DietPlanFactory::create($goal, $dietPreference);
                $generatedData = $generator->generate($weight);

                foreach ($generatedData['weekly_plan'] as $day => $mealsOfDay) {
                    foreach ($mealsOfDay as $mealKey => $meal) {
                        MealPlan::create([
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'meal_name' => $meal['meal_name'],
                            'day' => strtolower($day),
                            'time_period' => $meal['time_period'],
                            'food_data' => $meal['food_data'],
                            'calories' => $meal['calories'],
                            'protein' => $meal['protein'],
                            'carbs' => $meal['carbs'],
                            'fats' => $meal['fats'],
                        ]);
                    }
                }

                $plan->update([
                    'meta_data' => array_merge($metaData, [
                        'target_calories' => $generatedData['nutrition_targets']['calories'],
                        'target_protein' => $generatedData['nutrition_targets']['protein'],
                        'target_carbs' => $generatedData['nutrition_targets']['carbs'],
                        'target_fats' => $generatedData['nutrition_targets']['fats'],
                    ])
                ]);

                DB::commit();

                $meals = MealPlan::where('plan_uuid', $plan->uuid)->get();
            } else {
                $meals = MealPlan::where('plan_uuid', $plan->uuid)->get();
            }

            $plan->setRelation('meals', $meals);

            return Response::json([
                'plan_uuid' => $plan->uuid,
                'routine' => new \App\Http\Resources\DietRoutineResource($plan),
                'available_weighted_units' => Helper::getAvailableDietWeightedUnits(),
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to get diet routine', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Get available foods based on user preference.
     */
    public function getAvailableFoods(Request $request)
    {
        try {
            $user = Auth::user();
            $dietType = $request->query('type', $user->user_fitness_data['diet_preference'] ?? 'veg');

            if (!$user->food_data) {
                $foodData = FoodLibrary::getAllFoods();
                $user->update(['food_data' => $foodData]);
            }

            $foods = FoodLibrary::getFoodsByType($dietType);

            return Response::json([
                'foods' => $foods,
                'type' => $dietType,
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            Helper::logError('Unable to get available foods', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Delete a diet plan and its meal plans.
     */
    public function deleteMealPlan(string $uuid)
    {
        try {
            $user = Auth::user();

            DB::beginTransaction();

            MealPlan::where('uuid', $uuid)
            ->where('user_uuid', $user->uuid)
            ->delete();

            DB::commit();
            return Response::json(['message' => 'Diet plan deleted successfully'], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to delete diet plan', [__CLASS__, __FUNCTION__], $e);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Update the diet routine.
     */
    public function saveDietPlanRoutine (Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'plan_uuid' => 'required|uuid',
                'routine' => 'required|array',
            ]);

            $plan = Plan::where('uuid', $data['plan_uuid'] ?? null)
                ->where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->firstOrFail();

            DB::beginTransaction();

            foreach ($data['routine'] as $day => $meals) {
                foreach ($meals as $meal) {
                    $mealUuid = $meal['uuid'] ?? (string) \Illuminate\Support\Str::uuid();
                    $totals = DietService::calculateTotals($meal['food_data'] ?? []);

                    MealPlan::updateOrCreate(
                        [
                            'uuid' => $mealUuid,
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'day' => strtolower($day),
                        ],
                        [
                            'meal_name' => $meal['meal_name'] ?? 'Custom Meal',
                            'time_period' => $meal['time_period'] ?? 'snack',
                            'food_data' => $meal['food_data'] ?? [],
                            'calories' => $totals['calories'],
                            'protein' => $totals['protein'],
                            'carbs' => $totals['carbs'],
                            'fats' => $totals['fats'],
                            'nutrition_data' => $meal['nutrition_data'] ?? null,
                        ]
                    );
                }
            }


            DB::commit();
            return Response::json(['message' => 'Diet routine updated successfully'], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to update diet routine', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }
}
