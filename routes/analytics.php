<?php

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/workout/log', [AnalyticsController::class, 'workoutLog']);
    Route::get('/analytics/workout/insights', [AnalyticsController::class, 'workoutInsights']);
    Route::get('/analytics/workout/available-exercises', [AnalyticsController::class, 'getAvailableExercises']);
    Route::get('/analytics/workout/progressive-overload', [AnalyticsController::class, 'progressiveOverload']);
    Route::get('/analytics/workout/muscle-distribution', [AnalyticsController::class, 'muscleDistribution']);
});
