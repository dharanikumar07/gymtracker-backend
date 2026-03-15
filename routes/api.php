<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\PhysicalActivityController;
use App\Http\Controllers\Api\DietController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/verify-email/{uuid}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Onboarding Routes
    Route::get('/onboarding/physical-activity', [OnboardingController::class, 'getPhysicalActivityData']);
    Route::post('/onboarding/complete', [OnboardingController::class, 'completeOnboarding']);

    // Routine Routes
    Route::get('/routine', [PhysicalActivityController::class, 'getRoutine']);
    Route::patch('/routine', [PhysicalActivityController::class, 'updateRoutine']);
    Route::get('/routine/tracking', [PhysicalActivityController::class, 'getTrackingData']);
    Route::post('/routine/tracking', [PhysicalActivityController::class, 'saveTrackingData']);

    // Diet Routes
    Route::get('/diet/routine', [DietController::class, 'getDietRoutine']);
    Route::post('/diet/routine', [DietController::class, 'generatePlan']);
    Route::patch('/diet/routine', [DietController::class, 'updateDietRoutine']);
    Route::post('/diet/routine/active', [DietController::class, 'setActivePlan']);
    Route::get('/diet/tracking', [DietController::class, 'getDietLogs']);
    Route::post('/diet/tracking', [DietController::class, 'saveDietLog']);
});
