<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class SettingsController extends Controller
{
    /**
     * Get user profile information.
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();

            return Response::json([
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'fitness_data' => $user->user_fitness_data ?? []
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError(
                'Error occurred in getProfile',
                [__CLASS__, __FUNCTION__],
                $e,
                []
            );

            return Response::json([
                'message' => 'Server Error Occurred'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user profile and fitness data.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'user_fitness_data' => $request->input('fitness_data'),
            ]);

            DB::commit();

            return Response::json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'fitness_data' => $user->user_fitness_data
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();

            Helper::logError(
                'Error occurred in updateProfile',
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
