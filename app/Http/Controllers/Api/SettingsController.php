<?php

namespace App\Http\Controllers\Api;

use App\Helpers\SettingsManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Helpers\Helper;
use App\Services\NotificationScheduleSyncService;
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

    /**
     * Get settings based on type.
     */
    public function getSettings(Request $request)
    {
        try {
            $user = Auth::user();
            $type = $request->input('type', 'notification');
            
            $manager = new SettingsManager($type, $user->uuid);
            $settings = $manager->get()->getSettings();

            // If settings are empty, provide defaults through mergeWithDefault
            if (empty($settings)) {
                $settings = $manager->mergeWithDefault([]);
            }

            return Response::json([
                'data' => $settings,
                'message' => 'Settings retrieved successfully'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError(
                'Error occurred in getSettings',
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
     * Save settings based on type.
     */
    public function saveSettings(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            
            $type = $request->input('type', 'notification');
            $manager = new SettingsManager($type, $user->uuid);

            // Save settings using the manager (handles mergeWithDefault)
            $manager->save($request->all());

            // Sync notification schedules when saving notification settings
            if ($type === 'notification') {
                $syncService = new NotificationScheduleSyncService();
                $syncService->sync($user->uuid, $manager->getSettings());
            }

            DB::commit();

            return Response::json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'data' => $manager->getSettings()
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollback();

            Helper::logError(
                'Error occurred in saveSettings',
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
