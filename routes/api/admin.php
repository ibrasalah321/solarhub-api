<?php

use App\Http\Controllers\Auth\Admin\AdminApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {

        // Engineer Approval
        Route::get('/engineers/pending', [
            AdminApprovalController::class,
            'pendingEngineers',
        ]);

        Route::patch('/engineers/{engineer}/approve', [
            AdminApprovalController::class,
            'approveEngineer',
        ]);

        Route::patch('/engineers/{engineer}/reject', [
            AdminApprovalController::class,
            'rejectEngineer',
        ]);

        // Store Approval
        Route::get('/stores/pending', [
            AdminApprovalController::class,
            'pendingStores',
        ]);

        Route::patch('/stores/{store}/approve', [
            AdminApprovalController::class,
            'approveStore',
        ]);

        Route::patch('/stores/{store}/reject', [
            AdminApprovalController::class,
            'rejectStore',
        ]);
    });