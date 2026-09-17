<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderStoreController;
use App\Http\Controllers\Api\QuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Cart (single cart per authenticated customer).
    Route::get('/my/cart', [CartController::class, 'show']);
    Route::delete('/my/cart', [CartController::class, 'destroy']);
    Route::post('/my/cart/items', [CartItemController::class, 'store']);
    Route::put('/my/cart/items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('/my/cart/items/{cartItem}', [CartItemController::class, 'destroy']);

    // Orders (customer side).
    Route::get('/my/orders', [OrderController::class, 'index']);
    Route::post('/my/orders', [OrderController::class, 'store']);
    Route::get('/my/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/my/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Order-store branches (store owner side + customer delivery confirmation).
    Route::get('/my/store-orders', [OrderStoreController::class, 'index']);
    Route::get('/order-stores/{orderStore}', [OrderStoreController::class, 'show']);
    Route::patch('/order-stores/{orderStore}/status', [OrderStoreController::class, 'updateStatus']);
    Route::patch('/order-stores/{orderStore}/confirm-delivery', [OrderStoreController::class, 'confirmDelivery']);

    // Quote requests (negotiated pricing between customer and store).
    Route::get('/my/quote-requests', [QuoteRequestController::class, 'myRequests']);
    Route::get('/my/store-quote-requests', [QuoteRequestController::class, 'myStoreRequests']);
    Route::post('/quote-requests', [QuoteRequestController::class, 'store']);
    Route::patch('/quote-requests/{quoteRequest}/respond', [QuoteRequestController::class, 'respond']);
    Route::patch('/quote-requests/{quoteRequest}/accept', [QuoteRequestController::class, 'accept']);
    Route::patch('/quote-requests/{quoteRequest}/reject', [QuoteRequestController::class, 'reject']);
});
