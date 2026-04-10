<?php

use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ExpenseLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'getExpense']);
        Route::post('/', [ExpenseController::class, 'saveExpense']);
    });
    Route::get('/log', [ExpenseLogController::class, 'index']);
    Route::post('/log', [ExpenseLogController::class, 'log']);
    Route::delete('/log/{uuid}', [ExpenseLogController::class, 'destroy']);
});
