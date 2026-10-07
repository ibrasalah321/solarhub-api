<?php

use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Notifcication\NotificationTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my/notifications', [NotificationController::class, 'index']);
    Route::get('/my/notifications/inbox', [NotificationController::class, 'inbox']);
    Route::get('/my/notifications/outbox', [NotificationController::class, 'outbox']);

    Route::patch('/my/notifications/{notification}/read', [
        NotificationController::class,
        'markAsRead',
    ]);

    Route::patch('/my/notifications/read-all', [
        NotificationController::class,
        'markAllAsRead',
    ]);

    Route::middleware('permission:notification-templates.manage')->group(function () {
        Route::get('/notification-templates', [NotificationTemplateController::class, 'index']);
        Route::get('/notification-templates/{notificationTemplate}', [
            NotificationTemplateController::class,
            'show',
        ]);
        Route::post('/notification-templates', [NotificationTemplateController::class, 'store']);
        Route::put('/notification-templates/{notificationTemplate}', [
            NotificationTemplateController::class,
            'update',
        ]);
        Route::delete('/notification-templates/{notificationTemplate}', [
            NotificationTemplateController::class,
            'destroy',
        ]);
    });
});
