<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
| This file is required from routes/api.php, which is already mounted
| under the "/api" prefix by bootstrap/app.php. Every path below is
| therefore reachable as /api/auth/... — do not add another "api/" prefix
| here.
*/

// Guest-only: registration, email verification, login, password reset.
Route::middleware('guest')->group(function () {
    Route::post('/auth/register', [RegistrationController::class, 'register']);

    Route::post('/auth/otp/verify', [OtpController::class, 'verify'])
        ->middleware('throttle:otp-verify');

    Route::post('/auth/otp/resend', [OtpController::class, 'resend'])
        ->middleware('throttle:otp-resend');

    Route::post('/auth/login', [AuthenticationController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('/auth/forgot-password', [PasswordController::class, 'forgotPassword'])
        ->middleware('throttle:forgot-password');

    Route::post('/auth/reset-password', [PasswordController::class, 'resetPassword']);
});

// Authenticated via Sanctum token.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthenticationController::class, 'logout']);
    Route::get('/auth/me', [AuthenticationController::class, 'me']);
});
