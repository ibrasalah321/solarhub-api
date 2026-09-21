<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GovernorateController;
use App\Http\Controllers\Api\MasterProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProductSpecificationController;
use Illuminate\Support\Facades\Route;

// Public catalog browsing.
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}', [BrandController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/governorates', [GovernorateController::class, 'index']);
Route::get('/governorates/{governorate}', [GovernorateController::class, 'show']);

Route::get('/master-products', [MasterProductController::class, 'index']);
Route::get('/master-products/{masterProduct}', [MasterProductController::class, 'show']);

// Admin catalog management.
// NOTE: no role/permission system yet — protected by auth:sanctum only. Add a role/policy gate before production.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands', [BrandController::class, 'store']);
    Route::put('/brands/{brand}', [BrandController::class, 'update']);
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/governorates', [GovernorateController::class, 'store']);
    Route::put('/governorates/{governorate}', [GovernorateController::class, 'update']);
    Route::delete('/governorates/{governorate}', [GovernorateController::class, 'destroy']);

    Route::post('/master-products', [MasterProductController::class, 'store']);
    Route::put('/master-products/{masterProduct}', [MasterProductController::class, 'update']);
    Route::delete('/master-products/{masterProduct}', [MasterProductController::class, 'destroy']);

    Route::post('/product-specifications', [ProductSpecificationController::class, 'store']);
    Route::put('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'update']);
    Route::delete('/product-specifications/{productSpecification}', [ProductSpecificationController::class, 'destroy']);

    Route::post('/product-images', [ProductImageController::class, 'store']);
    Route::patch('/product-images/{productImage}/feature', [ProductImageController::class, 'markFeatured']);
    Route::delete('/product-images/{productImage}', [ProductImageController::class, 'destroy']);
});
