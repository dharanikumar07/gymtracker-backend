<?php

use App\Http\Controllers\Api\WorkoutsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('workouts')->group(function () {
        Route::get('/physical-activity', [WorkoutsController::class, 'getPhysicalActivity']);
        Route::post('/physical-activity', [WorkoutsController::class, 'savePhysicalActivity']);
        Route::delete('/workout-slot/{uuid}', [WorkoutsController::class, 'deleteWorkoutSlot']);
    });
});
