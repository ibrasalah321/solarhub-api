<?php

use App\Http\Controllers\Api\Favorite\FavoriteController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/', [FavoriteController::class, 'store']);
    Route::delete('/{store_product_id}', [FavoriteController::class, 'destroy']);
    Route::post('/{store_product_id}/toggle', [FavoriteController::class, 'toggle']);
});