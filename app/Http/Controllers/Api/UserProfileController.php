<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFitnessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function getProfile()
    {
        $profile = Auth::user()->userFitnessProfile;
        return response()->json($profile);
    }

    public function updateStep(Request $request)
    {
        $user = Auth::user();
        $step = $request->input('step'); // 1 or 2
        $inputData = $request->input('data', []);

        $profile = $user->userFitnessProfile()->firstOrCreate(
            ['user_id' => $user->id],
            ['steps_completed' => 0, 'data' => []]
        );

        // Merge existing JSON data with new data
        $currentData = $profile->data ?? [];
        $newData = array_merge($currentData, $inputData);

        $profile->update([
            'data' => $newData,
            'steps_completed' => max($profile->steps_completed, $step)
        ]);

        return response()->json([
            'message' => "Step $step completed successfully",
            'profile' => $profile
        ]);
    }
}
