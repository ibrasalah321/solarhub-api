<?php

use App\Http\Controllers\Api\Enginner\EngineerCertificateController;
use App\Http\Controllers\Api\Enginner\EngineerProfileController;
use App\Http\Controllers\Api\Enginner\PortfolioItemController;
use App\Http\Controllers\Auth\Engineer\EngineerOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/engineers', [EngineerProfileController::class, 'index']);
Route::get('/engineers/{engineer}', [EngineerProfileController::class, 'show']);
Route::get('/engineers/{engineer}/portfolio-items', [
    PortfolioItemController::class,
    'publicIndex',
]);

Route::middleware(['auth:sanctum', 'role:engineer'])
    ->prefix('engineer')
    ->group(function () {
        Route::post('/onboarding', [EngineerOnboardingController::class, 'store']);
        Route::get('/onboarding/status', [EngineerOnboardingController::class, 'status']);

        Route::get('/profile', [EngineerProfileController::class, 'myProfile']);

        Route::put('/profile', [EngineerProfileController::class, 'update'])
            ->middleware('permission:engineer-profiles.update');

        Route::get('/certificates', [EngineerCertificateController::class, 'index']);

        Route::post('/certificates', [EngineerCertificateController::class, 'store'])
            ->middleware([
                'permission:engineer-certificates.create',
                'can:create,App\Models\EngineerCertificate',
            ]);

        Route::delete('/certificates/{certificate}', [
            EngineerCertificateController::class,
            'destroy',
        ])->middleware([
            'permission:engineer-certificates.delete',
            'can:delete,certificate',
        ]);

        Route::get('/portfolio-items', [PortfolioItemController::class, 'index']);

        Route::post('/portfolio-items', [PortfolioItemController::class, 'store'])
            ->middleware([
                'permission:portfolio-items.create',
                'can:create,App\Models\PortfolioItem',
            ]);

        Route::put('/portfolio-items/{portfolioItem}', [
            PortfolioItemController::class,
            'update',
        ])->middleware([
            'permission:portfolio-items.update',
            'can:update,portfolioItem',
        ]);

        Route::delete('/portfolio-items/{portfolioItem}', [
            PortfolioItemController::class,
            'destroy',
        ])->middleware([
            'permission:portfolio-items.delete',
            'can:delete,portfolioItem',
        ]);
    });
