<?php

use App\Http\Controllers\Auth\Admin\AdminApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {
        Route::get('/engineers/pending', [
            AdminApprovalController::class,
            'pendingEngineers',
        ])->middleware('permission:engineers.view-pending');

        Route::patch('/engineers/{engineer}/approve', [
            AdminApprovalController::class,
            'approveEngineer',
        ])->middleware('permission:engineers.approve');

        Route::patch('/engineers/{engineer}/reject', [
            AdminApprovalController::class,
            'rejectEngineer',
        ])->middleware('permission:engineers.reject');

        Route::get('/stores/pending', [
            AdminApprovalController::class,
            'pendingStores',
        ])->middleware('permission:stores.view-pending');

        Route::patch('/stores/{store}/approve', [
            AdminApprovalController::class,
            'approveStore',
        ])->middleware('permission:stores.approve');

        Route::patch('/stores/{store}/reject', [
            AdminApprovalController::class,
            'rejectStore',
        ])->middleware('permission:stores.reject');
    });
