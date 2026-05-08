<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DietLogRequest;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\MealPlan;
use App\Models\DietLog;
use App\Http\Helpers\Helper;
use App\Http\Resources\DietLogResource;
use App\Http\Resources\MealPlanResource;
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

            info($dayOfWeek);

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->where('is_active', true)
                ->first();

            throw_if(!$activePlan, new \Exception('No active diet plan found'));

            $logs = DietLog::with('mealPlan')
                ->where('user_uuid', $user->uuid)
                ->where('plan_uuid', $activePlan->uuid)
                ->where('day', $dayOfWeek)
                ->whereDate('activity_date', $date->toDateString())
                ->orderBy('created_at')
                ->get();

            $templateMeals = MealPlan::where('plan_uuid', $activePlan->uuid)
                ->where('day', $dayOfWeek)
                ->get();

            $pending = $this->getPendingMeals($templateMeals, $logs);

            $metaData = $activePlan->meta_data ?? [];

            return Response::json([
                'activity_date' => $date->toDateString(),
                'day' => $dayOfWeek,
                'data' => DietLogResource::collection($logs),
                'pending' => $pending,
                'total_targets' => [
                    'calories' => $metaData['target_calories'] ?? null,
                    'protein' => $metaData['target_protein'] ?? null,
                    'carbs' => $metaData['target_carbs'] ?? null,
                    'fats' => $metaData['target_fats'] ?? null,
                ],
                'summary' => [
                    'total_planned' => $templateMeals->count(),
                    'total_completed' => $logs->count(),
                    'total_pending' => count($pending),
                ],
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Throwable $exception) {
            Helper::logError('Unable to get diet logs', [__CLASS__, __FUNCTION__], $exception);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Save diet logs.
     */
    public function saveDietLog(DietLogRequest $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            $plan = Plan::where('uuid', $data['plan_uuid'])
                ->where('user_uuid', $user->uuid)
                ->where('type', 'diet')
                ->firstOrFail();

            DB::beginTransaction();

            foreach ($data['logs'] as $logData) {
                $activityDate = Carbon::parse($data['date']);


                $logAttributes = [
                    'user_uuid' => $user->uuid,
                    'meal_plan_uuid' => $logData['meal_plan_uuid'] ?? null,
                    'plan_uuid' => $plan->uuid,
                    'day' => strtolower($data['day']),
                    'activity_date' => $activityDate->toDateString(),
                ];

                $logValues = [
                    'food_data' => $logData['food_data'] ?? [],
                    'calories' => $logData['calories'] ?? 0,
                    'protein' => $logData['protein'] ?? 0,
                    'carbs' => $logData['carbs'] ?? 0,
                    'fats' => $logData['fats'] ?? 0,
                    'type' => $logData['type'] ?? null,
                    'status' => $logData['status'] ?? 'completed',
                    'reason' => $logData['reason'] ?? null,
                ];

                $dietLog = DietLog::updateOrCreate($logAttributes, $logValues);

                if ($logData['sync_with_setup'] ?? false) {
                    $mealPlan = MealPlan::where('uuid', $logData['meal_plan_uuid'])
                        ->where('user_uuid', $user->uuid)
                        ->first();

                    if ($mealPlan) {
                        $mealPlan->update([
                            'meal_name' => $logData['meal_name'],
                            'time_period' => $logData['time_period'],
                            'food_data' => $logData['food_data'] ?? [],
                            'calories' => $logData['calories'] ?? 0,
                            'protein' => $logData['protein'] ?? 0,
                            'carbs' => $logData['carbs'] ?? 0,
                            'fats' => $logData['fats'] ?? 0,
                        ]);
                    }
                }
            }

            DB::commit();
            return Response::json(['message' => 'Diet progress saved successfully'], HttpFoundationResponse::HTTP_OK);

        } catch (\Throwable $exception) {
            DB::rollBack();
            Helper::logError('Unable to save diet log', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteDietLog($uuid)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $log = DietLog::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->first();

            throw_if(!$log, new \Exception('Diet log not found'));

            $log->delete();

            DB::commit();

            return Response::json([
                'message' => 'Diet log deleted successfully',
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Throwable $exception) {
            DB::rollBack();
            Helper::logError('Unable to delete diet log', [__CLASS__, __FUNCTION__], $exception, []);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getPendingMeals($templateMeals, $logs): array
    {
        $loggedMealUuids = $logs->pluck('meal_plan_uuid')->toArray();

        return MealPlanResource::collection(
            $templateMeals->whereNotIn('uuid', $loggedMealUuids)->values()
        )->resolve();
    }
}
