<?php

use App\Http\Controllers\Api\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('onboarding')->group(function () {
        Route::get('/profile-information', [OnboardingController::class, 'getProfileInformation']);
        Route::post('/profile-information', [OnboardingController::class, 'saveProfileInformation']);
        Route::get('/physical-activity', [OnboardingController::class, 'getPhysicalActivityData']);
        Route::post('/complete', [OnboardingController::class, 'completeOnboarding']);
    });
});
