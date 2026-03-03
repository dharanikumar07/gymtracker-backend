<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\UserProfileController;
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
    Route::get('/me', [UserProfileController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Onboarding and Profile Routes
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
    Route::get('/onboarding/data', [UserProfileController::class, 'getOnboardingData']);
    Route::post('/onboarding/complete', [UserProfileController::class, 'completeOnboarding']);
});
