<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\PhysicalActivityController;
use App\Http\Controllers\Api\DietController;
use App\Http\Controllers\Api\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
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

        // Expense Routes
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses/log', [ExpenseController::class, 'log']);
        Route::delete('/expenses/{uuid}', [ExpenseController::class, 'destroy']);
        
        // Budget Plan Routes
        Route::get('/expenses/budget-plan', [ExpenseController::class, 'getBudgetPlans']);
        Route::post('/expenses/budget-plan', [ExpenseController::class, 'saveBudgetPlan']);
        Route::get('/expenses/budget-plan/status/{uuid}', [ExpenseController::class, 'getBudgetPlanStatus']);
        Route::patch('/expenses/budget-plan/{uuid}', [ExpenseController::class, 'updateBudgetPlan']);
        Route::delete('/expenses/budget-plan/{uuid}', [ExpenseController::class, 'deleteBudgetPlan']);
        Route::post('/expenses/budget-plan/{uuid}/activate', [ExpenseController::class, 'activateBudgetPlan']);

        // Analytics Routes
        require base_path('routes/analytics.php');

        // Dashboard Routes
        require base_path('routes/dashboard.php');
    });
});
