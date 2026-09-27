<?php

use App\Http\Controllers\Api\Settings\PlatformSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [PlatformSettingController::class, 'show']);

Route::middleware(['auth:sanctum', 'permission:settings.manage'])
    ->put('/settings', [PlatformSettingController::class, 'update']);
