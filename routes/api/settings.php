<?php

use App\Http\Controllers\Api\PlatformSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [PlatformSettingController::class, 'show']);

// Platform settings update — administrators only.
Route::middleware(['auth:sanctum', 'permission:settings.manage'])
    ->put('/settings', [PlatformSettingController::class, 'update']);
