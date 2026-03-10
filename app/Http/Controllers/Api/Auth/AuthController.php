<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use App\Http\Resources\MeResource;
use Carbon\Carbon;
use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function me()
    {
        try {
            return new MeResource(Auth::user());
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to Get user',
                [__CLASS__, __FUNCTION__],
                $exception
            );

            return Response::json(
                ['error' => 'An error occurred', 'message' => $exception->getMessage()],
                HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR
            );
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

            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_onboarding_completed' => false,
            ]);

            event(new Registered($user));

            return Response::json([
                'message' => 'User registered successfully.'
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
                return Response::json([
                    'message' => 'Invalid login details'
                ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
            }

            if (!$user->hasVerifiedEmail()) {
                return Response::json([
                    'message' => 'Your email address is not verified.'
                ], HttpFoundationResponse::HTTP_FORBIDDEN);
            }

            return $this->issueTokens($user);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to login user',
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

    private function issueTokens(User $user)
    {
        // Access token expires in 30 minutes
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(30))->plainTextToken;
        
        // Refresh token expires in 2 days
        $refreshToken = $user->createToken('refresh_token', ['refresh-token'], now()->addDays(2))->plainTextToken;

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
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function refresh(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken();

            if ($token->name !== 'refresh_token') {
                return Response::json(['message' => 'Invalid token type'], HttpFoundationResponse::HTTP_UNAUTHORIZED);
            }

            $request->user()->tokens()->delete();

            return $this->issueTokens($request->user());
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to refresh token',
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

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return Response::json([
                'message' => 'Logged out successfully'
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to logout',
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
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed',
                'token' => 'required|string'
            ]);

            $resetData = DB::table('password_reset_tokens')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token,
                ])->first();

            if (!$resetData) {
                return Response::json(['message' => 'Invalid token or email'], HttpFoundationResponse::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();
            $user->update(['password' => Hash::make($request->password)]);

            DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();

            return Response::json(['message' => 'Your password has been reset!'], HttpFoundationResponse::HTTP_OK);
        } catch (\Exception $exception) {
            Helper::logError(
                'Unable to reset password',
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
}
