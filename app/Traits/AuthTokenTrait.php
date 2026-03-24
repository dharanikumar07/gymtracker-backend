<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Response;

trait AuthTokenTrait
{
    /**
     * Issue access and refresh tokens for a user.
     */
    protected function issueTokens(User $user)
    {
        // Access token expires in 50 minutes
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(50))->plainTextToken;
        
        // Refresh token expires in 7 days
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(7))->plainTextToken;

        return Response::json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'uuid' => $user->uuid,
                'is_email_verified' => !empty($user->email_verified_at),
                'is_onboarding_completed' => $user->is_onboarding_completed,
            ]
        ], 200);
    }
}
