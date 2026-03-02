<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Social login callback (API response)
Route::get('/auth/callback/{provider}', [AuthController::class, 'handleProviderCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Onboarding and Profile Routes
    Route::get('/profile', [\App\Http\Controllers\Api\UserProfileController::class, 'getProfile']);
    Route::post('/onboarding/step', [\App\Http\Controllers\Api\UserProfileController::class, 'updateStep']);
});
