<?php

namespace App\Http\Controllers\Api;

use App\Data\PhysicalActivityData\AbstractPhysicalActivity;
use App\Data\PhysicalActivityData\PhysicalActivityFactory;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use App\Models\PhysicalActivitySlot;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MeResource;

class OnboardingController extends Controller
{
    public function getPhysicalActivityData(Request $request)
    {
        try {
            $type = $request->query('type');

            $physicalActivityClass = (new PhysicalActivityFactory($type))->getPhysicalActivityClass();

            $data = $physicalActivityClass->getData();

            return Response::json($data, 200);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to get physical activity data',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['message' => 'An error occurred'],
                500
            );
        }
    }

    public function completeOnboarding(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'profile' => 'required|array',
                'plan' => 'required|array',
                'routine' => 'required|array',
                'steps_completed' => 'required|array',
                'expenses' => 'array'
            ]);

            DB::beginTransaction();

            // 1. Update User Profile Data
            $user->update([
                'user_fitness_data' => $data['profile'],
                'is_onboarding_completed' => true
            ]);

            // 2. Create Physical Activity Plan
            $plan = Plan::create([
                'user_uuid' => $user->uuid,
                'name' => $data['plan']['name'],
                'type' => Plan::PHYSICAL_ACTIVITY_TYPE,
                'start_date' => $data['plan']['start_date'] ?? null,
                'end_date' => $data['plan']['end_date'] ?? null,
                'is_active' => $data['plan']['is_active'] ?? true,
            ]);

            // 3. Create Physical Activity Slots (Weekly Routine)
            foreach ($data['routine'] as $day => $dayInfo) {
                // Skip if it's not an array with workouts
                if (!isset($dayInfo['workouts']) || !is_array($dayInfo['workouts']))
                    continue;

                foreach ($dayInfo['workouts'] as $index => $workout) {
                    // Check if it's a rest day (metrics type 'rest')
                    $metricsType = $workout['metrics']['type'] ?? 'rest';

                    PhysicalActivitySlot::create([
                        'user_uuid' => $user->uuid,
                        'plan_uuid' => $plan->uuid,
                        'exercise_name' => $workout['name'] ?? 'Rest Day',
                        'exercise_order' => $index + 1,
                        'day' => strtolower($day), // Migration has enum mon, tue, etc
                        'metrics_type' => $metricsType,
                        'metrics_data' => $workout['metrics']['data'] ?? [],
                        'meta_data' => null // As requested
                    ]);
                }
            }

            if(!empty($data['expenses'] ?? [])) {
                (new ExpenseService())->storeBulk($data['expenses']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Onboarding completed successfully',
                'user' => new MeResource($user->fresh())
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();
            Helper::logError(
                'Unable to complete onboarding',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                500
            );
        }
    }
}
