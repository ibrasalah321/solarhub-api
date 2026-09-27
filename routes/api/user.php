<?php

use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/my/profile', [UserController::class, 'updateProfile']);
    Route::delete('/my/account', [UserController::class, 'destroy']);

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::patch('/users/{user}/status', [
            UserController::class,
            'updateStatus',
        ]);
    });
});
