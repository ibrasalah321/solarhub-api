<?php

use App\Http\Controllers\Api\PlatformSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [PlatformSettingController::class, 'show']);

// NOTE: no role/permission system yet — protected by auth:sanctum only. Add a role/policy gate before production.
Route::middleware('auth:sanctum')->put('/settings', [PlatformSettingController::class, 'update']);
