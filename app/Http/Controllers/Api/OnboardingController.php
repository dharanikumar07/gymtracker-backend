<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\ProfileInformationRequest;
use App\Http\Helpers\Helper;
use App\Data\PhysicalActivityData\PhysicalActivityFactory;
use App\Models\Plan;
use App\Models\PhysicalActivitySlot;
use App\Services\ExpenseService;
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

            DB::commit();

            return Response::json([
                'message' => 'Profile information saved successfully',
                'data' => $updatedProfileData
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

    public function getPhysicalActivityData(Request $request)
    {
        try {
            $type = $request->query('type');

            $physicalActivityClass = (new PhysicalActivityFactory($type))->getPhysicalActivityClass();
            $data = $physicalActivityClass->getData();

            DB::commit();

            return Response::json($data, HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();

            Helper::logError(
                'Unable to get physical activity data',
                [__CLASS__, __FUNCTION__],
                $e,
                $request->toArray()
            );

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
