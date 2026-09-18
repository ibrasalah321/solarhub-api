<?php

use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\StoreProductController;
use App\Http\Controllers\Api\StoreRatingController;
use Illuminate\Support\Facades\Route;

// Public storefront browsing.
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{store}', [StoreController::class, 'show']);
Route::get('/stores/{store}/ratings', [StoreRatingController::class, 'storeRatings']);

Route::get('/store-products', [StoreProductController::class, 'index']);
Route::get('/store-products/{storeProduct}', [StoreProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    // Store profile (customer applies to become a store; store owner manages their own profile).
    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{store}', [StoreController::class, 'update']);

    // Store approval — intended for platform administrators.
    // NOTE: no role/permission system yet — protected by auth:sanctum only. Add a role/policy gate before production.
    Route::patch('/stores/{store}/approval', [StoreController::class, 'updateApproval']);

    // Store owner's own product listings.
    Route::get('/my/store-products', [StoreProductController::class, 'myListings']);
    Route::post('/store-products', [StoreProductController::class, 'store']);
    Route::put('/store-products/{storeProduct}', [StoreProductController::class, 'update']);
    Route::delete('/store-products/{storeProduct}', [StoreProductController::class, 'destroy']);

    // Store ratings (customer submits/manages a rating for a completed order).
    Route::post('/store-ratings', [StoreRatingController::class, 'store']);
    Route::put('/store-ratings/{storeRating}', [StoreRatingController::class, 'update']);
    Route::delete('/store-ratings/{storeRating}', [StoreRatingController::class, 'destroy']);
});
