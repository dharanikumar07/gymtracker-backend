<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFitnessProfile;
use App\Models\ExpenseTracker;
use App\Http\Resources\MeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserProfileController extends Controller
{
    /**
     * Get the authenticated user's profile summary.
     */
    public function me()
    {
        return new MeResource(Auth::user());
    }

    public function getProfile()
    {
        $user = Auth::user();
        $profile = $user->fitnessProfile;
        $expenses = $user->expenseTracker;
        return response()->json([
            'profile' => $profile,
            'expenses' => $expenses
        ]);
    }

    public function getOnboardingData()
    {
        $onboardingData = [
            'strength_training' => [
                'Mon' => ['Chest Press', 'Incline Dumbbell Fly', 'Barbell Biceps Curl', 'Hammer Curl', 'Push-Ups'],
                'Tue' => ['Dumbbell Triceps Extension', 'Triceps Pushdown', 'Lat Pulldown', 'Seated Cable Row', 'Deadlift', 'Plank'],
                'Wed' => ['Barbell Squat', 'Leg Press', 'Overhead Press', 'Lateral Raise', 'Leg Curl', 'Calf Raise'],
                'Thu' => ['Rest'],
                'Fri' => ['Chest Press', 'Incline Dumbbell Fly', 'Barbell Biceps Curl', 'Hammer Curl', 'Push-Ups'],
                'Sat' => ['Dumbbell Triceps Extension', 'Triceps Pushdown', 'Lat Pulldown', 'Seated Cable Row', 'Deadlift', 'Plank'],
                'Sun' => ['Barbell Squat', 'Leg Press', 'Overhead Press', 'Lateral Raise', 'Leg Curl', 'Calf Raise'],
            ],
            'cardio' => [
                'Mon' => ['Walking (20 min)'],
                'Tue' => ['Rest'],
                'Wed' => ['Cycling (15–20 min)'],
                'Thu' => ['Rest'],
                'Fri' => ['Jogging (10–15 min)'],
                'Sat' => ['Brisk Walk / Light HIIT'],
                'Sun' => ['Rest'],
            ],
            'flexibility' => [
                'Mon' => ['Full Body Stretch (10 min)'],
                'Tue' => ['Rest'],
                'Wed' => ['Hip & Hamstring Mobility'],
                'Thu' => ['Rest'],
                'Fri' => ['Shoulder & Back Mobility'],
                'Sat' => ['Yoga Flow (Light)'],
                'Sun' => ['Rest'],
            ],
            'balance' => [
                'Mon' => ['Core Stability (Planks, Dead Bugs)'],
                'Tue' => ['Rest'],
                'Wed' => ['Balance Training (Single-leg work)'],
                'Thu' => ['Rest'],
                'Fri' => ['Stability Flow (Bands / Bodyweight)'],
                'Sat' => ['Light Walk / Mobility'],
                'Sun' => ['Rest'],
            ],
        ];

        return response()->json($onboardingData);
    }

    public function completeOnboarding(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'profile' => 'required|array',
            'routine' => 'required|array',
            'expenses' => 'required|array',
            'steps_completed' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. Update Fitness Profile and set is_onboarding_completed here
            UserFitnessProfile::updateOrCreate(
                ['user_uuid' => $user->uuid],
                [
                    'data' => [
                        'personal_info' => $data['profile'],
                        'weekly_split' => $data['routine'],
                    ],
                    'steps_completed' => $data['steps_completed'],
                    'is_onboarding_completed' => true
                ]
            );

            // 2. Update Expense Tracker
            ExpenseTracker::updateOrCreate(
                ['user_uuid' => $user->uuid],
                [
                    'data' => $data['expenses']
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Onboarding completed successfully',
                'user' => new MeResource($user->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to complete onboarding: ' . $e->getMessage()], 500);
        }
    }
}
