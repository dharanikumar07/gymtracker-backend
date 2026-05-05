<?php

use App\Http\Controllers\Api\DietController;
use App\Http\Controllers\Api\DietLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/diet', [DietController::class, 'getDietRoutine']);
    Route::get('/diet/available-foods', [DietController::class, 'getAvailableFoods']);
    Route::post('/diet', [DietController::class, 'saveDietPlanRoutine']);
    Route::delete('/diet/{uuid}', [DietController::class, 'deleteMealPlan']);

    Route::prefix('diet')->group(function () {
        Route::get('/log', [DietLogController::class, 'getDietLogs']);
        Route::post('/log', [DietLogController::class, 'saveDietLog']);
    });
});
