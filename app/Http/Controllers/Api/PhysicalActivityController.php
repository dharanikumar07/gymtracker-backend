<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use App\Models\PhysicalActivityTracker;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Carbon\Carbon;

class PhysicalActivityController extends Controller
{
    private function getAvailableUnits()
    {
        return [
            'weight_units' => ['kg', 'lbs', 'pounds'],
            'duration_units' => ['seconds', 'minutes', 'hours']
        ];
    }

    /**
     * Get the current active routine/schedule.
     */
    public function getRoutine()
    {
        try {
            $user = Auth::user();
            
            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'physical_activity')
                ->where('is_active', true)
                ->with(['physicalActivitySlots' => function($query) {
                    $query->orderBy('exercise_order');
                }])
                ->first();

            if (!$activePlan) {
                return Response::json(['message' => 'No active plan found'], HttpFoundationResponse::HTTP_NOT_FOUND);
            }

            $routine = [];
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            
            foreach ($days as $day) {
                $daySlots = $activePlan->physicalActivitySlots->where('day', $day);
                
                $routine[ucfirst($day)] = [
                    'workouts' => $daySlots->map(function($slot) {
                        return [
                            'uuid' => $slot->uuid,
                            'name' => $slot->{'exercise_name'},
                            'order' => $slot->{'exercise_order'},
                            'metrics' => [
                                'type' => $slot->metrics_type,
                                'data' => $slot->metrics_data
                            ],
                            'sample_video_link' => $slot->meta_data['sample_video_link'] ?? ''
                        ];
                    })->values()->toArray()
                ];
            }

            return Response::json([
                'plan' => [
                    'uuid' => $activePlan->uuid,
                    'name' => $activePlan->name,
                    'start_date' => $activePlan->start_date,
                    'end_date' => $activePlan->end_date,
                ],
                'routine' => $routine,
                'units' => $this->getAvailableUnits()
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to get routine',
                [__CLASS__, __FUNCTION__],
                $exception
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get tracking data for a specific date.
     */
    public function getTrackingData(Request $request)
    {
        try {
            $user = Auth::user();
            $dateStr = $request->query('date', Carbon::today()->toDateString());
            $date = Carbon::parse($dateStr);
            $dayOfWeek = strtolower($date->format('D'));

            $activePlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', 'physical_activity')
                ->where('is_active', true)
                ->first();

            if (!$activePlan) {
                return Response::json(['message' => 'No active plan found'], HttpFoundationResponse::HTTP_NOT_FOUND);
            }

            $slots = PhysicalActivitySlot::where('plan_uuid', $activePlan->uuid)
                ->where('day', $dayOfWeek)
                ->orderBy('exercise_order')
                ->get();

            $trackers = PhysicalActivityTracker::where('user_uuid', $user->uuid)
                ->where('activity_date', $date->toDateString())
                ->get()
                ->keyBy('slot_uuid');

            $data = $slots->map(function($slot) use ($trackers) {
                $tracker = $trackers->get($slot->uuid);
                return [
                    'slot_uuid' => $slot->uuid,
                    'exercise_name' => $slot->{' exercise_name'},
                    'metrics_type' => $slot->metrics_type,
                    'prescribed_metrics' => $slot->metrics_data,
                    'performed_metrics' => $tracker ? ($tracker->metrics_data['sets'] ?? []) : null,
                    'sample_video_link' => $slot->meta_data['sample_video_link'] ?? ''
                ];
            });

            return Response::json([
                'date' => $date->toDateString(),
                'day' => $dayOfWeek,
                'exercises' => $data,
                'units' => $this->getAvailableUnits()
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to get tracking data',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update the routine schedule.
     */
    public function updateRoutine(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'plan_uuid' => 'required|uuid',
                'routine' => 'required|array',
            ]);

            $plan = Plan::where('uuid', $data['plan_uuid'])
                ->where('user_uuid', $user->uuid)
                ->where('type', 'physical_activity')
                ->firstOrFail();

            DB::beginTransaction();

            PhysicalActivitySlot::where('plan_uuid', $plan->uuid)->delete();

            foreach ($data['routine'] as $day => $dayInfo) {
                if (!isset($dayInfo['workouts']) || !is_array($dayInfo['workouts'])) continue;

                foreach ($dayInfo['workouts'] as $index => $workout) {
                    $metricsType = $workout['metrics']['type'] ?? 'rest';
                    
                    PhysicalActivitySlot::create([
                        'user_uuid' => $user->uuid,
                        'plan_uuid' => $plan->uuid,
                        'exercise_name' => $workout['name'] ?? 'Rest Day',
                        'exercise_order' => $index + 1,
                        'day' => strtolower($day),
                        'metrics_type' => $metricsType,
                        'metrics_data' => $workout['metrics']['data'] ?? [],
                        'meta_data' => [
                            'sample_video_link' => $workout['sample_video_link'] ?? ''
                        ]
                    ]);
                }
            }

            DB::commit();

            return Response::json(['message' => 'Routine updated successfully'], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError(
                'Unable to update routine',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Save/Update tracking data for a specific date.
     */
    public function saveTrackingData(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'date' => 'required|date',
                'tracking' => 'required|array',
                'tracking.*.slot_uuid' => 'required|uuid',
                'tracking.*.performed_metrics' => 'nullable|array'
            ]);

            DB::beginTransaction();

            foreach ($data['tracking'] as $item) {
                PhysicalActivityTracker::updateOrCreate(
                    [
                        'user_uuid' => $user->uuid,
                        'slot_uuid' => $item['slot_uuid'],
                        'activity_date' => $data['date']
                    ],
                    [
                        'metrics_data' => ['sets' => $item['performed_metrics'] ?? []],
                        'year' => Carbon::parse($data['date'])->year
                    ]
                );
            }

            DB::commit();

            return Response::json(['message' => 'Workout progress saved successfully'], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError(
                'Unable to save tracking data',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
