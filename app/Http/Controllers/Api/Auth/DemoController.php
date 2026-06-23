<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Models\User;
use App\Traits\AuthTokenTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class DemoController extends Controller
{
    use AuthTokenTrait;

    /**
     * Generate demo credentials for preview.
     */
    public function generate()
    {
        $randomId = strtolower(Str::random(8));

        return Response::json([
            'name' => 'Demo User',
            'email' => "demo_{$randomId}@mailinator.in",
            'password' => Str::random(10),
        ], HttpFoundationResponse::HTTP_OK);
    }

    /**
     * Register a demo user (optionally with edited credentials).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'is_demo' => true,
                'demo_expires_at' => now()->addDays(7),
            ]);

            return $this->issueTokens($user);

        } catch (\Exception $e) {
            Helper::logError(
                'Demo registration failed',
                [__CLASS__, __FUNCTION__],
                $e,
                $request->only(['name', 'email'])
            );

            return Response::json([
                'message' => 'Failed to create demo account',
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
