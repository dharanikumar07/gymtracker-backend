<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\PhysicalActivityRequest;
use App\Http\Resources\PhysicalActivityResource;
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
}
