<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Models\UserDeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DeviceTokenController extends Controller
{
    /**
     * Save or update an FCM device token for the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'sometimes|string|in:web,android,ios',
        ]);

        try {
            $user = Auth::user();
            $fcmToken = $request->input('fcm_token');
            $deviceType = $request->input('device_type', 'web');

            // Match on fcm_token (including soft-deleted) to avoid unique constraint violations
            $token = UserDeviceToken::withTrashed()
                ->where('fcm_token', $fcmToken)
                ->first();

            if ($token) {
                $token->restore();
                $token->update([
                    'user_uuid' => $user->uuid,
                    'device_type' => $deviceType,
                    'is_active' => true,
                ]);
            } else {
                $token = UserDeviceToken::create([
                    'user_uuid' => $user->uuid,
                    'fcm_token' => $fcmToken,
                    'device_type' => $deviceType,
                    'is_active' => true,
                ]);
            }

            return Response::json([
                'message' => 'Device token saved successfully',
                'data' => [
                    'uuid' => $token->uuid,
                    'device_type' => $token->device_type,
                ]
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError(
                'Error occurred in DeviceTokenController@store',
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
     * Deactivate a device token (e.g. on logout).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        try {
            // Keep the token active so the user continues to receive
            // notifications even after logout. The token is only
            // deactivated when FCM reports it as UNREGISTERED.

            return Response::json([
                'message' => 'Device token retained'
            ], HttpFoundationResponse::HTTP_OK);

        } catch (\Exception $e) {
            Helper::logError(
                'Error occurred in DeviceTokenController@destroy',
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
