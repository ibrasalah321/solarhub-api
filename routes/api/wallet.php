<?php

use App\Http\Controllers\Api\Payment\OrderPaymentController;
use App\Http\Controllers\Api\Payment\StorePayoutController;
use App\Http\Controllers\Api\Wallet\UserWalletController;
use App\Http\Controllers\Api\Wallet\WalletProviderController;
use Illuminate\Support\Facades\Route;

Route::get('/wallet_providers', [WalletProviderController::class, 'index']);
Route::get('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('permission:wallet-providers.manage')->group(function () {
        Route::post('/wallet_providers', [WalletProviderController::class, 'store']);
        Route::put('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'update']);
        Route::delete('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'destroy']);
    });

    Route::get('/my/wallets', [UserWalletController::class, 'index']);
    Route::post('/my/wallets', [UserWalletController::class, 'store']);
    Route::get('/my/wallets/{userWallet}', [UserWalletController::class, 'show']);
    Route::put('/my/wallets/{userWallet}', [UserWalletController::class, 'update']);
    Route::delete('/my/wallets/{userWallet}', [UserWalletController::class, 'destroy']);

    Route::get('/orders/{order}/payments', [OrderPaymentController::class, 'index'])
        ->middleware('can:view,order');

    Route::post('/order_payments', [OrderPaymentController::class, 'store'])
        ->middleware('role:customer');

    Route::get('/order_payments/{orderPayment}', [OrderPaymentController::class, 'show'])
        ->middleware('can:view,orderPayment');

    Route::patch('/order_payments/{orderPayment}/status', [
        OrderPaymentController::class,
        'updateStatus',
    ])->middleware('permission:order-payments.verify');

    Route::middleware('permission:store-payouts.manage')->group(function () {
        Route::get('/store_payouts', [StorePayoutController::class, 'index']);
        Route::post('/store_payouts', [StorePayoutController::class, 'store']);
        Route::get('/store_payouts/{storePayout}', [StorePayoutController::class, 'show']);
        Route::put('/store_payouts/{storePayout}', [StorePayoutController::class, 'update']);
    });
});
