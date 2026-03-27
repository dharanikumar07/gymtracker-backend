<?php

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/fitness', [AnalyticsController::class, 'fitness']);
    Route::get('/analytics/diet', [AnalyticsController::class, 'diet']);
    Route::get('/analytics/expenses', [AnalyticsController::class, 'expenses']);
});
