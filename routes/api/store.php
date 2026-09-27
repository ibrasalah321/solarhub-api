<?php

use App\Http\Controllers\Api\Store\StoreController;
use App\Http\Controllers\Api\Store\StoreProductController;
use App\Http\Controllers\Api\Store\StoreRatingController;
use App\Http\Controllers\Auth\Store\StoreOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::get('/stores/{store}/ratings', [StoreRatingController::class, 'storeRatings']);

Route::get('/store-products', [StoreProductController::class, 'index']);
Route::get('/store-products/{storeProduct}', [StoreProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stores', [StoreOnboardingController::class, 'store'])
        ->middleware([
            'role:supplier',
            'permission:stores.create',
            'can:create,App\Models\Store',
        ]);

    Route::put('/stores/{store}', [StoreController::class, 'update'])
        ->middleware([
            'permission:stores.update',
            'can:update,store',
        ]);

    Route::get('/my/store-products', [StoreProductController::class, 'myListings'])
        ->middleware('role:supplier');

    Route::post('/store-products', [StoreProductController::class, 'store'])
        ->middleware([
            'permission:store-products.create',
            'approved',
            'can:create,App\Models\StoreProduct',
        ]);

    Route::put('/store-products/{storeProduct}', [
        StoreProductController::class,
        'update',
    ])->middleware([
        'permission:store-products.update',
        'approved',
        'can:update,storeProduct',
    ]);

    Route::delete('/store-products/{storeProduct}', [
        StoreProductController::class,
        'destroy',
    ])->middleware([
        'permission:store-products.delete',
        'approved',
        'can:delete,storeProduct',
    ]);

    Route::post('/store-ratings', [StoreRatingController::class, 'store'])
        ->middleware('permission:store-ratings.create');

    Route::put('/store-ratings/{storeRating}', [
        StoreRatingController::class,
        'update',
    ])->middleware('permission:store-ratings.update');

    Route::delete('/store-ratings/{storeRating}', [
        StoreRatingController::class,
        'destroy',
    ])->middleware('permission:store-ratings.delete');
});
