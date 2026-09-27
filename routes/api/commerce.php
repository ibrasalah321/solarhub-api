<?php

use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Cart\CartItemController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Order\OrderStoreController;
use App\Http\Controllers\Api\QuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:customer')->group(function () {
        Route::get('/my/cart', [CartController::class, 'show']);
        Route::delete('/my/cart', [CartController::class, 'destroy']);
        Route::post('/my/cart/items', [CartItemController::class, 'store']);
        Route::put('/my/cart/items/{cartItem}', [CartItemController::class, 'update']);
        Route::delete('/my/cart/items/{cartItem}', [CartItemController::class, 'destroy']);
    });

    Route::get('/my/orders', [OrderController::class, 'index'])
        ->middleware('role:customer');

    Route::post('/my/orders', [OrderController::class, 'store'])
        ->middleware('permission:orders.create');

    Route::get('/my/orders/{order}', [OrderController::class, 'show'])
        ->middleware(['permission:orders.view', 'can:view,order']);

    Route::patch('/my/orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->middleware(['permission:orders.cancel', 'can:cancel,order']);

    Route::get('/my/store-orders', [OrderStoreController::class, 'index'])
        ->middleware('role:supplier');

    Route::get('/order-stores/{orderStore}', [OrderStoreController::class, 'show'])
        ->middleware('can:view,orderStore');

    Route::patch('/order-stores/{orderStore}/status', [
        OrderStoreController::class,
        'updateStatus',
    ])->middleware([
        'permission:orders.manage-status',
        'approved',
        'can:updateStatus,orderStore',
    ]);

    Route::patch('/order-stores/{orderStore}/confirm-delivery', [
        OrderStoreController::class,
        'confirmDelivery',
    ])->middleware([
        'role:customer',
        'can:confirmDelivery,orderStore',
    ]);

    Route::get('/my/quote-requests', [QuoteRequestController::class, 'myRequests'])
        ->middleware('role:customer');

    Route::get('/my/store-quote-requests', [
        QuoteRequestController::class,
        'myStoreRequests',
    ])->middleware('role:supplier');

    Route::post('/quote-requests', [QuoteRequestController::class, 'store'])
        ->middleware('permission:quote-requests.create');

    Route::patch('/quote-requests/{quoteRequest}/respond', [
        QuoteRequestController::class,
        'respond',
    ])->middleware([
        'permission:quote-requests.respond',
        'approved',
        'can:respond,quoteRequest',
    ]);

    Route::patch('/quote-requests/{quoteRequest}/accept', [
        QuoteRequestController::class,
        'accept',
    ])->middleware([
        'permission:quote-requests.accept',
        'can:accept,quoteRequest',
    ]);

    Route::patch('/quote-requests/{quoteRequest}/reject', [
        QuoteRequestController::class,
        'reject',
    ])->middleware([
        'permission:quote-requests.reject',
        'can:reject,quoteRequest',
    ]);
});
