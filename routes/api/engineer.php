<?php

use App\Http\Controllers\Api\Enginner\EngineerProfileController;
use App\Http\Controllers\Api\Enginner\EngineerCertificateController;
use App\Http\Controllers\Api\Enginner\PortfolioItemController;
use App\Http\Controllers\Auth\Engineer\EngineerOnboardingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Engineer Routes
|--------------------------------------------------------------------------
*/

Route::get('/engineers', [
    EngineerProfileController::class,
    'index',
]);

Route::get('/engineers/{engineer}', [
    EngineerProfileController::class,
    'show',
]);

Route::get('/engineers/{engineer}/portfolio-items', [
    PortfolioItemController::class,
    'publicIndex',
]);

/*
|--------------------------------------------------------------------------
| Authenticated Engineer Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('engineer')
    ->group(function () {

        Route::post('/onboarding', [
            EngineerOnboardingController::class,
            'store',
        ]);

        Route::get('/onboarding/status', [
            EngineerOnboardingController::class,
            'status',
        ]);

        Route::get('/profile', [
            EngineerProfileController::class,
            'myProfile',
        ]);

        Route::put('/profile', [
            EngineerProfileController::class,
            'update',
        ]);

        Route::get('/certificates', [
            EngineerCertificateController::class,
            'index',
        ]);

        Route::post('/certificates', [
            EngineerCertificateController::class,
            'store',
        ]);

        Route::delete('/certificates/{certificate}', [
            EngineerCertificateController::class,
            'destroy',
        ]);

        Route::get('/portfolio-items', [
            PortfolioItemController::class,
            'index',
        ]);

        Route::post('/portfolio-items', [
            PortfolioItemController::class,
            'store',
        ]);

        Route::put('/portfolio-items/{portfolioItem}', [
            PortfolioItemController::class,
            'update',
        ]);

        Route::delete('/portfolio-items/{portfolioItem}', [
            PortfolioItemController::class,
            'destroy',
        ]);
    });