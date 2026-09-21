<?php

use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\StorePayoutController;
use App\Http\Controllers\Api\UserWalletController;
use App\Http\Controllers\Api\WalletProviderController;
use Illuminate\Support\Facades\Route;

// Public lookup: active wallet providers (e.g. for payment method selection screens).
Route::get('/wallet_providers', [WalletProviderController::class, 'index']);
Route::get('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    // Admin management of wallet providers (lookup table).
    Route::post('/wallet_providers', [WalletProviderController::class, 'store']);
    Route::put('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'update']);
    Route::delete('/wallet_providers/{walletProvider}', [WalletProviderController::class, 'destroy']);

    // Authenticated user's own payout wallets.
    Route::get('/my/wallets', [UserWalletController::class, 'index']);
    Route::post('/my/wallets', [UserWalletController::class, 'store']);
    Route::get('/my/wallets/{userWallet}', [UserWalletController::class, 'show']);
    Route::put('/my/wallets/{userWallet}', [UserWalletController::class, 'update']);
    Route::delete('/my/wallets/{userWallet}', [UserWalletController::class, 'destroy']);

    // Order payments (customer submits proof; admin verifies/rejects).
    Route::get('/orders/{order}/payments', [OrderPaymentController::class, 'index']);
    Route::post('/order_payments', [OrderPaymentController::class, 'store']);
    Route::get('/order_payments/{orderPayment}', [OrderPaymentController::class, 'show']);
    Route::patch('/order_payments/{orderPayment}/status', [OrderPaymentController::class, 'updateStatus']);

    // Store payouts (admin only).
    Route::get('/store_payouts', [StorePayoutController::class, 'index']);
    Route::post('/store_payouts', [StorePayoutController::class, 'store']);
    Route::get('/store_payouts/{storePayout}', [StorePayoutController::class, 'show']);
    Route::put('/store_payouts/{storePayout}', [StorePayoutController::class, 'update']);
});
