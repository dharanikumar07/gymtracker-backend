<?php

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    
    // Workout Analytics
    Route::get('/analytics/workout/log', [AnalyticsController::class, 'workoutLog']);
    Route::get('/analytics/workout/insights', [AnalyticsController::class, 'workoutInsights']);
    Route::get('/analytics/workout/available-exercises', [AnalyticsController::class, 'getAvailableExercises']);
    Route::get('/analytics/workout/progressive-overload', [AnalyticsController::class, 'progressiveOverload']);
    Route::get('/analytics/workout/muscle-distribution', [AnalyticsController::class, 'muscleDistribution']);

    // Expense Analytics
    Route::get('/analytics/expense/log', [AnalyticsController::class, 'expenseLog']);
    Route::get('/analytics/expense/insights', [AnalyticsController::class, 'expenseInsights']);
    Route::get('/analytics/expense/available-categories', [AnalyticsController::class, 'getAvailableCategories']);
    Route::get('/analytics/expense/trend', [AnalyticsController::class, 'expenseTrend']);
    Route::get('/analytics/expense/distribution', [AnalyticsController::class, 'expenseDistribution']);
});
