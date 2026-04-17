<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkoutLogRequest;
use App\Http\Resources\WorkoutLogResource;
use App\Http\Helpers\Helper;
use App\Models\PhysicalActivitySlot;
use App\Models\PhysicalActivityTracker;
use App\Models\Plan;
use App\Services\WorkoutService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Http\Request;

class WorkoutLogController extends Controller
{
    protected $workoutService;

    public function __construct(WorkoutService $workoutService)
    {
        $this->workoutService = $workoutService;
    }
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $activityDate = $request->query('activity_date');
            $planUuid = $request->query('plan_uuid');
            $day = strtolower($request->query('day'));
            
            throw_if(!$day || !$activityDate, new Exception("activity_date, day  and plan_uuid is required"));

            $plan = Plan::findByUuid($planUuid);

            $physcialActivityType = $plan->meta_data['physical_activity_type'] ?? null;

            $logs = PhysicalActivityTracker::with('slot')
                ->where('user_uuid', $user->uuid)
                ->where('plan_uuid', $plan->uuid)
                ->where('activity_date', $activityDate)
                ->where('day', $day)
                ->orderBy('created_at')
                ->get();

            $template = $this->workoutService->getWorkoutSlotByDay($plan->uuid, $user->uuid, $day);
            $pending = $this->workoutService->getPendingTemplateWorkouts($logs, $plan->uuid, $user->uuid, $day);

            return Response::json([
                'activity_date' => $activityDate,
                'day' => $day,
                'data' => WorkoutLogResource::collection($logs),
                'pending' => $pending,
                'metrics_defaults' => $this->workoutService->getMetricsDefaultUnitsMetrics($physcialActivityType),
                'summary' => [
                    'total_planned' => count($template['slots']),
                    'total_completed' => $logs->count(),
                    'total_pending' => count($pending),
                ],
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to fetch workout logs', [__CLASS__, __FUNCTION__], $exception);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/workouts/log
     * Save workout logs using updateOrCreate
     * If slot_uuid is null, create a new slot first with auto-incremented order
     */
    public function store(WorkoutLogRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $activityDate = $request->input('activity_date');
            $day = strtolower($request->input('day'));
            $planUuid = $request->input('plan_uuid');
            $logs = $request->input('logs');

            $plan = Plan::where('uuid', $planUuid)
                ->where('user_uuid', $user->uuid)
                ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
                ->first();

            throw_if(!$plan, new Exception("Plan not found"));

            $savedLogs = [];

            foreach ($logs as $log) {
                $slotUuid = $log['slot_uuid'] ?? null;
                $exerciseName = $log['exercise_name'] ?? null;
                $metricsType = $log['metrics_type'] ?? 'strength';
                $metricsData = $log['metrics_data'] ?? [];
                $type = $log['type'] ?? null;
                $status = $log['status'] ?? 'completed';
                $reason = $log['reason'] ?? null;

                if (!$slotUuid) {
                    $maxOrder = PhysicalActivitySlot::where('plan_uuid', $plan->uuid)
                        ->where('day', $day)
                        ->max('exercise_order') ?? 0;

                    $defaultMetricsData = $this->workoutService->getDefaultMetricsDataByType($metricsType);
                    $mergedMetricsData = array_merge($defaultMetricsData, $metricsData);

                    $slot = PhysicalActivitySlot::updateOrCreate(
                        [
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'day' => $day,
                        ],
                        [
                            'exercise_name' => $exerciseName,
                            'exercise_order' => $maxOrder + 1,
                            'metrics_type' => $metricsType,
                            'metrics_data' => $mergedMetricsData,
                        ]
                    );

                    $slotUuid = $slot->uuid;
                }

                throw_if(!$slotUuid, new Exception("slot_uuid is required"));

                $tracker = PhysicalActivityTracker::updateOrCreate(
                    [
                        'user_uuid' => $user->uuid,
                        'plan_uuid' => $plan->uuid,
                        'slot_uuid' => $slotUuid,
                        'activity_date' => $activityDate,
                    ],
                    [
                        'year' => date('Y', strtotime($activityDate)),
                        'day' => $day,
                        'metrics_data' => $metricsData,
                        'type' => $type,
                        'status' => $status,
                        'reason' => $reason,
                    ]
                );

                $savedLogs[] = $tracker;
            }

            DB::commit();

            return Response::json([
                'message' => 'Workout logs saved successfully',
                'data' => [
                    'logged_count' => count($savedLogs),
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to save workout logs', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
