<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\CronController;
use App\Http\Controllers\Api\ExpenseLogController;
use App\Http\Controllers\Api\PhysicalActivityController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Middleware\VerifyCronSecret;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Cron endpoints — protected by X-Cron-Secret header
    Route::prefix('cron')->group(function () {
        Route::get('/ping', [CronController::class, 'ping']); // No secret needed — just a wake-up call
        Route::post('/min', [CronController::class, 'min'])->middleware(VerifyCronSecret::class);
        Route::post('/twice-per-day', [CronController::class, 'twicePerDay'])->middleware(VerifyCronSecret::class);
        Route::post('/process-jobs', [CronController::class, 'processJobs'])->middleware(VerifyCronSecret::class);
    });

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirectToProvider']);
    Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'handleProviderCallback']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/verify-email/{uuid}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Plan Routes
        Route::get('/plans', [PlanController::class, 'getPlans']);
        Route::post('/plans', [PlanController::class, 'savePlan']);
        Route::patch('/plans/status', [PlanController::class, 'updatePlanStatus']);
        Route::delete('/plans/{uuid}', [PlanController::class, 'deletePlan']);

        // Settings Routes
        require base_path('routes/settings.php');

        // Analytics Routes
        require base_path('routes/analytics.php');

        // Dashboard Routes
        require base_path('routes/dashboard.php');
    });
});
