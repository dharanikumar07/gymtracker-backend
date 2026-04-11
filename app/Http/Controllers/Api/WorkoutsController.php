<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\PhysicalActivityRequest;
use App\Http\Requests\SlotsRequest;
use App\Http\Resources\PhysicalActivityResource;
use App\Http\Resources\SlotResource;
use App\Http\Helpers\Helper;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class WorkoutsController extends Controller
{
    public function getPhysicalActivity()
    {
        $user = Auth::user();
        
        $plan = Plan::where('user_uuid', $user->uuid)
            ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
            ->first();

        throw_if(!$plan, new Exception('No physical activity plan found'));

        $slots = PhysicalActivitySlot::where('plan_uuid', $plan->uuid)
            ->where('user_uuid', $user->uuid)
            ->orderBy('exercise_order')
            ->get();

        return (new PhysicalActivityResource([
            'plan' => $plan,
            'slots' => $slots,
        ]))->response();
    }

    public function savePhysicalActivity(PhysicalActivityRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $planUuid = $request->input('plan.uuid');

            $plan = Plan::where('uuid', $planUuid)
                ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
                ->where('user_uuid', $user->uuid)
                ->first();

            throw_if(!$plan, new Exception('No Plan found'));
            
            $plan->update([
                'name' => $request->input('plan.name'),
                'type' => Plan::PHYSICAL_ACTIVITY_TYPE,
                'meta_data' => [
                    'physical_activity_type' => $request->input('plan.physical_activity_type')
                ],
                'start_date' => $request->input('plan.start_date'),
                'end_date' => $request->input('plan.end_date'),
                'is_active' => filter_var($request->input('plan.is_active') ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);

            $weeklySplit = $request->input('weekly_split') ?? [];
            $exerciseOrder = 1;

            foreach ($weeklySplit as $dayName => $dayData) {
                $dayLower = strtolower(explode('-', $dayName)[0]);
                $targetMuscles = $dayData['target_muscles'] ?? [];
                $workouts = $dayData['workouts'] ?? [];

                foreach ($workouts as $workout) {
                    $slotUuid = $workout['uuid'] ?? null;
                    
                    if ($slotUuid) {
                        PhysicalActivitySlot::where('uuid', $slotUuid)
                            ->where('user_uuid', $user->uuid)
                            ->update([
                                'exercise_name' => $workout['name'] ?? '',
                                'exercise_order' => $workout['exercise_order'] ?? $exerciseOrder,
                                'day' => $dayLower,
                                'metrics_type' => $workout['metrics']['type'] ?? null,
                                'metrics_data' => $workout['metrics']['data'] ?? null,
                                'meta_data' => [
                                    'sample_video_link' => $workout['sample_video_link'] ?? null,
                                    'target_muscles' => $targetMuscles,
                                ],
                            ]);
                    } else {
                        PhysicalActivitySlot::create([
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                            'exercise_name' => $workout['name'] ?? '',
                            'exercise_order' => $workout['exercise_order'] ?? $exerciseOrder,
                            'day' => $dayLower,
                            'metrics_type' => $workout['metrics']['type'] ?? null,
                            'metrics_data' => $workout['metrics']['data'] ?? null,
                            'meta_data' => [
                                'sample_video_link' => $workout['sample_video_link'] ?? null,
                                'target_muscles' => $targetMuscles,
                            ],
                        ]);
                    }

                    $exerciseOrder++;
                }
            }

            DB::commit();

            return Response::json([
                'message' => 'Physical activity saved successfully',
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to save physical activity', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteWorkoutSlot($uuid)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $slot = PhysicalActivitySlot::where('uuid', $uuid)
                ->where('user_uuid', $user->uuid)
                ->first();

            throw_if(!$slot, new Exception('Workout slot not found'));

            $slot->delete();

            DB::commit();

            return Response::json([
                'message' => 'Workout slot deleted successfully',
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to delete workout slot', [__CLASS__, __FUNCTION__], $e, []);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get slots by plan_uuid.
     */
    public function getSlots(\Illuminate\Http\Request $request)
    {
        try {
            $user = Auth::user();
            $planUuid = $request->query('plan_uuid');

            throw_if(!$planUuid, new Exception('plan_uuid is required'));

            $slots = PhysicalActivitySlot::where('plan_uuid', $planUuid)
                ->where('user_uuid', $user->uuid)
                ->orderBy('day')
                ->orderBy('exercise_order')
                ->get();

            return Response::json([
                'data' => SlotResource::collection($slots)
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            Helper::logError('Unable to fetch slots', [__CLASS__, __FUNCTION__], $exception);
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Save/Update slots using updateOrCreate.
     */
    public function saveSlots(SlotsRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $slots = $request->input('slots');
            $savedSlots = [];

            foreach ( $slots as $slotData) {
                $slot = PhysicalActivitySlot::updateOrCreate(
                    [
                        'uuid' => $slotData['uuid'] ?? null,
                        'user_uuid' => $user->uuid,
                    ],
                    [
                        'plan_uuid' => $slotData['plan_uuid'],
                        'exercise_name' => $slotData['exercise_name'],
                        'exercise_order' => $slotData['exercise_order'] ?? 1,
                        'day' => $slotData['day'],
                        'metrics_type' => $slotData['metrics_type'] ?? null,
                        'metrics_data' => $slotData['metrics_data'] ?? [],
                        'meta_data' => $slotData['meta_data'] ?? [],
                    ]
                );

                $savedSlots[] = new SlotResource($slot);
            }

            DB::commit();

            return Response::json([
                'message' => 'Slots saved successfully',
                'data' => $savedSlots
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Error | \Exception $exception) {
            DB::rollBack();
            Helper::logError('Unable to save slots', [__CLASS__, __FUNCTION__], $exception, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
