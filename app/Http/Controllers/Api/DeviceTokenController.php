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

            $token = UserDeviceToken::updateOrCreate(
                [
                    'user_uuid' => $user->uuid,
                    'device_type' => $request->input('device_type', 'web'),
                ],
                [
                    'fcm_token' => $request->input('fcm_token'),
                    'is_active' => true,
                ]
            );

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
            UserDeviceToken::where('fcm_token', $request->input('fcm_token'))
                ->where('user_uuid', Auth::user()->uuid)
                ->update(['is_active' => false]);

            return Response::json([
                'message' => 'Device token deactivated'
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
