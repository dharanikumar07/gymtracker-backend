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

    public function completeOnboarding(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validate([
                'expenses' => 'array'
            ]);

            if (!empty($data['expenses'] ?? [])) {
                (new ExpenseService())->storeBulk($data['expenses']);
            }

            $user->update([
                'is_onboarding_completed' => true
            ]);

            return Response::json([
                'message' => 'Onboarding completed successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
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
