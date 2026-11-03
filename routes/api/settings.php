<?php

use App\Http\Controllers\Api\Settings\PlatformSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [PlatformSettingController::class, 'show']);

Route::middleware(['auth:sanctum', 'permission:settings.manage'])
    ->put('/settings', [PlatformSettingController::class, 'update']);

Route::get('/geo/reverse-geocode', function (\Illuminate\Http\Request $request, \App\Services\Geocoding\ReverseGeocodingService $service) {
    $request->validate([
        'lat' => ['required', 'numeric', 'between:-90,90'],
        'lng' => ['required', 'numeric', 'between:-180,180'],
    ]);

    return response()->json([
        'status' => 'success',
        'data' => $service->reverseGeocode((float) $request->input('lat'), (float) $request->input('lng')),
    ]);
});
