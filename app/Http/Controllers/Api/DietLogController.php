<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\MealPlan;
use App\Models\DietLog;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Carbon\Carbon;

class DietLogController extends Controller
{
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

            $planMeals = MealPlan::where('plan_uuid', $activePlan->uuid)
                ->where('day', $dayOfWeek)
                ->get();

            $logs = DietLog::where('user_uuid', $user->uuid)
                ->whereDate('created_at', $date->toDateString())
                ->get()
                ->keyBy('meal_plan_uuid');

            $data = [];
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

            foreach ($mealTypes as $meal) {
                $mealItems = $planMeals->where('time_period', $meal);
                $data[$meal] = $mealItems->map(function($item) use ($logs) {
                    $log = $logs->get($item->uuid);
                    return [
                        'meal_plan_uuid' => $item->uuid,
                        'meal_name' => $item->meal_name,
                        'food_data' => $item->food_data,
                        'prescribed' => [
                            'calories' => $item->calories,
                            'macros' => ['p' => $item->protein, 'c' => $item->carbs, 'f' => $item->fats]
                        ],
                        'logged' => $log ? [
                            'uuid' => $log->uuid,
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
                'logs.*.meal_plan_uuid' => 'required|uuid',
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
                        'meal_plan_uuid' => $logData['meal_plan_uuid'],
                        'plan_uuid' => $activePlan->uuid,
                        'day' => strtolower($date->format('D')),
                    ],
                    [
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
