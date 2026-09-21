<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/my/profile', [UserController::class, 'updateProfile']);
    Route::delete('/my/account', [UserController::class, 'destroy']);

    // Administrative user management.
    // NOTE: no role/permission system yet — protected by auth:sanctum only. Add a role/policy gate before production.
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
});
