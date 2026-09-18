<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my/notifications', [NotificationController::class, 'index']);
    Route::patch('/my/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/my/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Administrative template management.
    // NOTE: no role/permission system yet — protected by auth:sanctum only. Add a role/policy gate before production.
    Route::get('/notification-templates', [NotificationTemplateController::class, 'index']);
    Route::get('/notification-templates/{notificationTemplate}', [NotificationTemplateController::class, 'show']);
    Route::post('/notification-templates', [NotificationTemplateController::class, 'store']);
    Route::put('/notification-templates/{notificationTemplate}', [NotificationTemplateController::class, 'update']);
    Route::delete('/notification-templates/{notificationTemplate}', [NotificationTemplateController::class, 'destroy']);
});
