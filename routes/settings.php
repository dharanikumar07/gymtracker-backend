<?php

use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::get('/profile', [SettingsController::class, 'getProfile']);
    Route::post('/profile', [SettingsController::class, 'updateProfile']);
    
    // Notification Settings
    Route::get('/notifications', [SettingsController::class, 'getSettings']);
    Route::post('/notifications', [SettingsController::class, 'saveSettings']);
});
