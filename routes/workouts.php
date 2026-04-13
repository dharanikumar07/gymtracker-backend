<?php

use App\Http\Controllers\Api\WorkoutsController;
use App\Http\Controllers\Api\WorkoutLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('workouts')->group(function () {
        Route::get('/physical-activity', [WorkoutsController::class, 'getPhysicalActivity']);
        Route::post('/physical-activity', [WorkoutsController::class, 'savePhysicalActivity']);
        Route::delete('/workout-slot/{uuid}', [WorkoutsController::class, 'deleteWorkoutSlot']);
        
        Route::get('/slots', [WorkoutsController::class, 'getSlots']);
        Route::post('/slots', [WorkoutsController::class, 'saveSlots']);

        Route::get('/log', [WorkoutLogController::class, 'index']);
        Route::post('/log', [WorkoutLogController::class, 'store']);
    });
});
