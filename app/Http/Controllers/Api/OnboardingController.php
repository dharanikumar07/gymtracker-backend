<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\ProfileInformationRequest;
use App\Http\Requests\Onboarding\PhysicalActivityRequest;
use App\Http\Resources\PhysicalActivityResource;
use App\Http\Helpers\Helper;
use App\Data\PhysicalActivityData\PhysicalActivityFactory;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use App\Services\ExpenseService;
use App\Services\OnboardingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class OnboardingController extends Controller
{
    public function getProfileInformation()
    {
        try {
            $user = Auth::user();
            $profileData = $user->user_fitness_data ?? [];

            $response = [
                'age' => $profileData['age'] ?? null,
                'gender' => $profileData['gender'] ?? null,
                'height' => $profileData['height'] ?? null,
                'weight' => $profileData['weight'] ?? null,
                'fitness_goal' => $profileData['fitness_goal'] ?? null,
                'physical_activity_type' => $profileData['physical_activity_type'] ?? null,
            ];

            return Response::json([
                'message' => 'Profile information retrieved successfully',
                'data' => $response
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError(
                'Unable to get profile information',
                [__CLASS__, __FUNCTION__],
                $e,
                []
            );

            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveProfileInformation(ProfileInformationRequest $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            $existingProfileData = $user->user_fitness_data ?? [];

            DB::beginTransaction();

            $updatedProfileData = array_merge($existingProfileData, $data);

            $user->update([
                'user_fitness_data' => $updatedProfileData
            ]);

            $type = $data['physical_activity_type'];

            // Check if plan already exists for this user with this type
            $existingPlan = Plan::where('user_uuid', $user->uuid)
                ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
                ->first();

            // Check if physical activity slots already exist for this plan
            $existingSlots = null;
            if ($existingPlan) {
                $existingSlots = PhysicalActivitySlot::where('plan_uuid', $existingPlan->uuid)
                    ->where('user_uuid', $user->uuid)
                    ->get();
            }

            if (!$existingPlan || !$existingSlots || !$existingSlots->isNotEmpty()) {
                $physicalActivityClass = (new PhysicalActivityFactory($type))->getPhysicalActivityClass();
                $physicalActivityData = $physicalActivityClass->getData();

                $planData = [
                    'name' => 'My Transformation Plan',
                    'start_date' => now()->toDateString(),
                    'end_date' => null,
                    'is_active' => true,
                ];

                (new OnboardingService())->createWeeklyRoutineData(
                    $type,
                    $physicalActivityData,
                    $planData,
                    $user->uuid
                );
            }

            DB::commit();

            return Response::json([
                'message' => 'Profile information saved successfully',
                'data' => $updatedProfileData,
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            Helper::logError(
                'Unable to save profile information',
                [__CLASS__, __FUNCTION__],
                $e,
                $request->toArray()
            );

            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPhysicalActivity()
    {
        try {
            $user = Auth::user();
            
            $plan = Plan::where('user_uuid', $user->uuid)
                ->where('type', Plan::PHYSICAL_ACTIVITY_TYPE)
                ->where('is_active', true)
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

        } catch (\Exception $e) {
            Helper::logError(
                'Unable to get physical activity',
                [__CLASS__, __FUNCTION__],
                $e,
                []
            );

            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function savePhysicalActivity(PhysicalActivityRequest $request)
    {
        try {
            $user = Auth::user();
            $data = $request->all();

            DB::beginTransaction();

            $planUuid = $data['plan']['uuid'] ?? null;

            $userFitnessData = $user->user_fitness_data ?? [];
            $physicalActivityType = $data['physical_activity_type'] ?? $userFitnessData['physical_activity_type'] ?? null;

            $plan = Plan::where('uuid', $planUuid)
                ->where('user_uuid', $user->uuid)
                ->first();

            $plan->update([
                'name' => $data['plan']['name'] ?? null,
                    'type' => Plan::PHYSICAL_ACTIVITY_TYPE,
                    'meta_data' => [
                        'physical_activity_type' => $data['plan']['physical_activity_type'] ?? null
                    ],
                    'start_date' => $data['plan']['start_date'] ?? null,
                    'end_date' => $data['plan']['end_date'] ?? null,
                    'is_active' => $data['plan']['is_active'] ?? null,
            ]);

            $weeklySplit = $data['weekly_split'] ?? [];
            $exerciseOrder = 1;

            foreach ($weeklySplit as $dayName => $dayData) {
                $dayLower = strtolower(explode('-', $dayName)[0]);
                $targetMuscles = $dayData['target_muscles'] ?? [];
                $workouts = $dayData['workouts'] ?? [];

                foreach ($workouts as $workout) {
                    PhysicalActivitySlot::updateOrCreate(
                        [
                            'user_uuid' => $user->uuid,
                            'plan_uuid' => $plan->uuid,
                        ],
                        [

                            'exercise_name' => $workout['name'] ?? '',
                            'exercise_order' => $workout['exercise_order'] ?? $exerciseOrder,
                            'day' => $dayLower,
                            'metrics_type' => $workout['metrics']['type'] ?? null,
                            'metrics_data' => $workout['metrics']['data'] ?? null,
                            'meta_data' => [
                                'sample_video_link' => $workout['sample_video_link'] ?? null,
                                'target_muscles' => $targetMuscles,
                            ],
                        ]
                    );

                    $exerciseOrder++;
                }
            }

            DB::commit();

            return Response::json([
                'message' => 'Physical activity saved successfully',
                'data' => [
                    'plan' => $plan,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            Helper::logError('Unable to save physical activity', [__CLASS__, __FUNCTION__], $e, $request->toArray());
            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
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

            $user->update([
                'user_fitness_data' => $data['profile'],
                'is_onboarding_completed' => true
            ]);

            $plan = Plan::create([
                'user_uuid' => $user->uuid,
                'name' => $data['plan']['name'],
                'type' => Plan::PHYSICAL_ACTIVITY_TYPE,
                'start_date' => $data['plan']['start_date'] ?? null,
                'end_date' => $data['plan']['end_date'] ?? null,
                'is_active' => $data['plan']['is_active'] ?? true,
            ]);

            foreach ($data['routine'] as $day => $dayInfo) {
                if (!isset($dayInfo['workouts']) || !is_array($dayInfo['workouts']))
                    continue;

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
                        'meta_data' => null
                    ]);
                }
            }

            if (!empty($data['expenses'] ?? [])) {
                (new ExpenseService())->storeBulk($data['expenses']);
            }

            DB::commit();

            return Response::json([
                'message' => 'Onboarding completed successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            Helper::logError(
                'Unable to complete onboarding',
                [__CLASS__, __FUNCTION__],
                $e,
                $request->toArray()
            );

            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
