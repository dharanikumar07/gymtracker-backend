<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuthTokenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Resources\MeResource;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use AuthTokenTrait;

    /**
     * Get the authenticated user.
     */
    public function me()
    {
        try {
            return new MeResource(Auth::user());
        } catch (\Exception $exception) {
            Helper::logError('Unable to Get user', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = null;
            DB::transaction(function () use ($request, &$user) {
                $user = User::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_onboarding_completed' => false,
                ]);

                $user->sendVeirfyEMailTOUser();
            });

            return Response::json([
                'message' => 'User registered successfully. Verification email sent.'
            ], HttpFoundationResponse::HTTP_CREATED);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to register user',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return Response::json(['message' => 'Invalid login details'], 401);
            }

            if (!$user->hasVerifiedEmail()) {
                return Response::json(['message' => 'Your email address is not verified.'], 403);
            }

            return $this->issueTokens($user);
        } catch (\Exception $exception) {
            Helper::logError('Unable to login user', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Refresh Token.
     */
    public function refresh(Request $request)
    {
        try {
            $bearerToken = $request->bearerToken();
            if (!$bearerToken) return Response::json(['message' => 'Refresh token missing'], 401);

            $token = PersonalAccessToken::findToken($bearerToken);

            if (!$token || $token->name !== 'refresh_token' || ($token->expires_at && $token->expires_at->isPast())) {
                return Response::json(['message' => 'Invalid or expired refresh token'], 401);
            }

            $user = $token->tokenable;
            $token->delete();

            return $this->issueTokens($user);
        } catch (\Exception $exception) {
            Helper::logError('Unable to refresh token', [__CLASS__, __FUNCTION__], $exception);
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return Response::json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $exception) {
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }

    public function verify(Request $request, $uuid, $hash)
    {
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();

            if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return Response::json(['message' => 'Invalid verification link'], HttpFoundationResponse::HTTP_FORBIDDEN);
            }

            if ($user->hasVerifiedEmail()) {
                $tokenData = $this->issueTokens($user);
                $content = $tokenData->getOriginalContent();
                return Response::json([
                    'message' => 'Email already verified.',
                    'already_verified' => true,
                    ...$content
                ], HttpFoundationResponse::HTTP_OK);
            }

            if ($user->markEmailAsVerified()) {
                event(new \Illuminate\Auth\Events\Verified($user));
            }

            $tokenData = $this->issueTokens($user);
            $content = $tokenData->getOriginalContent();
            
            return Response::json([
                'message' => 'Email verified successfully.',
                'already_verified' => false,
                ...$content
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to verify email',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email|exists:users,email']);

            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => now()
                ]
            );

            Mail::raw("Your password reset token is: $token", function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Password Notification');
            });

            return Response::json(['message' => 'We have e-mailed your password reset token!'], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to process forgot password',
                [__CLASS__, __FUNCTION__],
                $exception,
                $request->toArray()
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_onboarding_completed' => false,
            ]);

            event(new \Illuminate\Auth\Events\Registered($user));

            return Response::json(['message' => 'User registered successfully.'], 201);
        } catch (\Exception $exception) {
            return Response::json(['error' => 'An error occurred'], 500);
        }
    }
}
