<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\DietPlanItem;
use App\Models\DietLog;
use App\Http\Helpers\Helper;
use App\Data\DietPlanData\DietPlanFactory;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Carbon\Carbon;

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
            
            $query = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet');

            if ($planUuid) {
                $query->where('uuid', $planUuid);
            } else {
                $query->where('is_active', true);
            }

            $plan = $query->first();

            if (!$plan) {
                return Response::json([
                    'message' => 'Diet plan not found',
                    'available_plans' => Plan::where('user_uuid', $user->uuid)
                        ->where('type', 'diet')
                        ->select('uuid as plan_uuid', 'name')
                        ->get()
                ], HttpFoundationResponse::HTTP_NOT_FOUND);
            }

            $availablePlans = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->select('uuid as plan_uuid', 'name', 'is_active')
                ->get();

            $items = DietPlanItem::where('plan_uuid', $plan->uuid)->get();

            // Group by day and meal_type
            $routine = [];
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

            foreach ($days as $day) {
                $dayRoutine = [];
                foreach ($mealTypes as $meal) {
                    $dayRoutine[$meal] = $items->where('day', $day)->where('meal_type', $meal)->values();
                }
                $routine[ucfirst($day)] = $dayRoutine;
            }

            $generator = DietPlanFactory::create($plan->name, $user->user_fitness_data['diet_preference'] ?? 'veg');
            $generatedData = $generator->generate((float)($user->user_fitness_data['weight'] ?? 70));

            return Response::json([
                'plan' => [
                    'uuid' => $plan->uuid,
                    'name' => $plan->name,
                    'start_date' => $plan->start_date,
                    'end_date' => $plan->end_date,
                    'is_active' => $plan->is_active,
                    'target_calories' => $generatedData['nutrition_targets']['calories'],
                    'target_protein' => $generatedData['nutrition_targets']['protein'],
                    'target_carbs' => $generatedData['nutrition_targets']['carbs'],
                    'target_fats' => $generatedData['nutrition_targets']['fats'],
                ],
                'routine' => $routine,
                'available_plans' => $availablePlans
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            Helper::logError('Unable to get diet routine', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Generate a new diet plan from a goal.
     */
    public function generatePlan(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'goal' => 'required|string',
                'diet_preference' => 'required|string',
                'name' => 'nullable|string'
            ]);

            $fitnessData = $user->user_fitness_data;
            $weight = (float)($fitnessData['weight'] ?? 70);
            
            $generator = DietPlanFactory::create($data['goal'], $data['diet_preference']);
            $generatedData = $generator->generate($weight);

            DB::beginTransaction();

            // Deactivate other diet plans
            Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->update(['is_active' => false]);

            $plan = Plan::create([
                'user_uuid' => $user->uuid,
                'name' => $data['name'] ?? ucfirst(str_replace('_', ' ', $data['goal'])) . ' Plan',
                'type' => 'diet',
                'is_active' => true,
                'start_date' => Carbon::today(),
            ]);

            foreach ($generatedData['weekly_plan'] as $day => $meals) {
                foreach ($meals as $mealType => $items) {
                    foreach ($items as $item) {
                        DietPlanItem::create([
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'meal_type' => $mealType,
                            'day' => strtolower($day),
                            'food_name' => $item['food_name'],
                            'quantity' => $item['quantity'],
                            'unit' => $item['unit'],
                            'calories' => $item['calories'],
                            'protein' => $item['protein'],
                            'carbs' => $item['carbs'],
                            'fats' => $item['fats'],
                        ]);
                    }
                }
            }

            DB::commit();
            return Response::json([
                'message' => 'Diet plan generated successfully',
                'plan_uuid' => $plan->uuid
            ], HttpFoundationResponse::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to generate diet plan', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Set a plan as active.
     */
    public function setActivePlan(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate(['plan_uuid' => 'required|uuid']);

            DB::beginTransaction();

            Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->update(['is_active' => false]);

            Plan::where('uuid', $data['plan_uuid'])
                ->where('user_uuid', $user->uuid)
                ->update(['is_active' => true]);

            DB::commit();
            return Response::json(['message' => 'Plan set as active'], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Update the diet routine.
     */
    public function updateDietRoutine(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'plan_uuid' => 'required|uuid',
                'routine' => 'required|array',
            ]);

            $plan = Plan::where('uuid', $data['plan_uuid'])
                ->where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->firstOrFail();

            DB::beginTransaction();

            DietPlanItem::where('plan_uuid', $plan->uuid)->delete();

            foreach ($data['routine'] as $day => $meals) {
                foreach ($meals as $mealType => $items) {
                    if (!is_array($items)) continue;
                    foreach ($items as $item) {
                        DietPlanItem::create([
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'meal_type' => $mealType,
                            'day' => strtolower($day),
                            'food_name' => $item['food_name'],
                            'quantity' => $item['quantity'] ?? 0,
                            'unit' => $item['unit'] ?? 'g',
                            'calories' => $item['calories'] ?? 0,
                            'protein' => $item['protein'] ?? 0,
                            'carbs' => $item['carbs'] ?? 0,
                            'fats' => $item['fats'] ?? 0,
                            'nutrition_data' => $item['nutrition_data'] ?? null
                        ]);
                    }
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

    /**
     * Get diet tracking logs for a specific date.
     */
    public function getDietLogs(Request $request)
    {
        try {
            $user = Auth::user();
            $dateStr = $request->query('date', Carbon::today()->toDateString());
            $date = Carbon::parse($dateStr);
            $dayOfWeek = strtolower($date->format('D'));

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('is_active', true)
                ->first();

            if (!$activePlan) {
                return Response::json([
                    'date' => $date->toDateString(),
                    'day' => $dayOfWeek,
                    'meals' => [
                        'breakfast' => [],
                        'lunch' => [],
                        'dinner' => [],
                        'snack' => []
                    ],
                    'message' => 'No active diet plan found'
                ], HttpFoundationResponse::HTTP_OK);
            }

            $planItems = DietPlanItem::where('plan_uuid', $activePlan->uuid)
                ->where('day', $dayOfWeek)
                ->get();

            $logs = DietLog::where('user_uuid', $user->uuid)
                ->whereDate('created_at', $date->toDateString())
                ->get()
                ->keyBy('diet_plan_item_uuid');

            $data = [];
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

            foreach ($mealTypes as $meal) {
                $mealItems = $planItems->where('meal_type', $meal);
                $data[$meal] = $mealItems->map(function($item) use ($logs) {
                    $log = $logs->get($item->uuid);
                    return [
                        'diet_plan_item_uuid' => $item->uuid,
                        'food_name' => $item->food_name,
                        'prescribed' => [
                            'quantity' => $item->quantity,
                            'unit' => $item->unit,
                            'calories' => $item->calories,
                            'macros' => ['p' => $item->protein, 'c' => $item->carbs, 'f' => $item->fats]
                        ],
                        'logged' => $log ? [
                            'uuid' => $log->uuid,
                            'quantity' => $log->actual_quantity,
                            'unit' => $log->quantity_unit,
                            'calories' => $log->calories,
                            'macros' => ['p' => $log->protein, 'c' => $log->carbs, 'f' => $log->fats],
                            'notes' => $log->notes
                        ] : null
                    ];
                })->values();
            }

            return Response::json([
                'date' => $date->toDateString(),
                'day' => $dayOfWeek,
                'meals' => $data
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            Helper::logError('Unable to get diet logs', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Save diet logs.
     */
    public function saveDietLog(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'date' => 'required|date',
                'logs' => 'required|array',
                'logs.*.diet_plan_item_uuid' => 'required|uuid',
                'logs.*.actual_quantity' => 'required|numeric',
                'logs.*.unit' => 'required|string',
                'logs.*.calories' => 'nullable|integer',
                'logs.*.protein' => 'nullable|integer',
                'logs.*.carbs' => 'nullable|integer',
                'logs.*.fats' => 'nullable|integer',
                'logs.*.notes' => 'nullable|string',
            ]);

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('is_active', true)
                ->firstOrFail();

            DB::beginTransaction();

            foreach ($data['logs'] as $logData) {
                $date = Carbon::parse($data['date']);
                
                DietLog::updateOrCreate(
                    [
                        'user_uuid' => $user->uuid,
                        'diet_plan_item_uuid' => $logData['diet_plan_item_uuid'],
                        'plan_uuid' => $activePlan->uuid,
                        'day' => strtolower($date->format('D')),
                    ],
                    [
                        'actual_food_name' => $logData['food_name'] ?? null,
                        'actual_quantity' => $logData['actual_quantity'],
                        'quantity_unit' => $logData['unit'],
                        'calories' => $logData['calories'] ?? 0,
                        'protein' => $logData['protein'] ?? 0,
                        'carbs' => $logData['carbs'] ?? 0,
                        'fats' => $logData['fats'] ?? 0,
                        'notes' => $logData['notes'] ?? null,
                        'created_at' => $date,
                    ]
                );
            }

            DB::commit();
            return Response::json(['message' => 'Diet progress saved successfully'], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to save diet log', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }
}
