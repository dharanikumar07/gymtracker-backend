<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\ExpenseLogController;
use App\Http\Controllers\Api\PhysicalActivityController;
use App\Http\Controllers\Api\DietController;
use App\Http\Controllers\Api\DietLogController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PlanController;
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

        // Diet Routes
        require base_path('routes/diet.php');

        // Expense Routes
        Route::get('/expenses', [ExpenseLogController::class, 'index']);
        Route::post('/expenses/log', [ExpenseLogController::class, 'log']);
        Route::delete('/expenses/{uuid}', [ExpenseLogController::class, 'destroy']);
        
        // Budget Plan Routes
        Route::get('/expenses/budget-plan', [ExpenseController::class, 'getBudgetPlans']);
        Route::post('/expenses/budget-plan', [ExpenseController::class, 'saveBudgetPlan']);
        Route::get('/expenses/budget-plan/status/{uuid}', [ExpenseController::class, 'getBudgetPlanStatus']);
        Route::patch('/expenses/budget-plan/{uuid}', [ExpenseController::class, 'updateBudgetPlan']);
        Route::delete('/expenses/budget-plan/{uuid}', [ExpenseController::class, 'deleteBudgetPlan']);
        Route::post('/expenses/budget-plan/{uuid}/activate', [ExpenseController::class, 'activateBudgetPlan']);

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
