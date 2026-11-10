<?php

use App\Http\Controllers\Api\ServiceRequest\OfferController;
use App\Http\Controllers\Api\ServiceRequest\ServiceRequestController;
use App\Http\Controllers\Api\ServiceRequest\ServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/service-types', [ServiceTypeController::class, 'index']);
Route::get('/service-types/{serviceType}', [ServiceTypeController::class, 'show']);

// إدارة أنواع الخدمات وعرض كافة طلبات الخدمات: للمدير فقط
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/service-types', [ServiceTypeController::class, 'store']);
    Route::put('/service-types/{serviceType}', [ServiceTypeController::class, 'update']);
    Route::patch('/service-types/{serviceType}', [ServiceTypeController::class, 'update']);
    Route::delete('/service-types/{serviceType}', [ServiceTypeController::class, 'destroy']);

    Route::get('/service-requests', [
        ServiceRequestController::class,
        'index',
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    // طلبات العميل.
    Route::get('/my/service-requests', [
        ServiceRequestController::class,
        'myRequests',
    ])->middleware('role:customer');

    // الطلبات المفتوحة للمهندس المعتمد.
    Route::get('/service-requests/open', [
        ServiceRequestController::class,
        'openRequests',
    ])->middleware([
        'permission:service-requests.view-open',
        'approved',
    ]);

    Route::get('/service-requests/{serviceRequest}', [
        ServiceRequestController::class,
        'show',
    ])->whereNumber('serviceRequest')->middleware('can:view,serviceRequest');

    Route::post('/service-requests', [
        ServiceRequestController::class,
        'store',
    ])->middleware('permission:service-requests.create');

    Route::put('/service-requests/{serviceRequest}', [
        ServiceRequestController::class,
        'update',
    ])->whereNumber('serviceRequest')->middleware([
        'permission:service-requests.update',
        'can:update,serviceRequest',
    ]);

    Route::patch('/service-requests/{serviceRequest}/cancel', [
        ServiceRequestController::class,
        'cancel',
    ])->whereNumber('serviceRequest')->middleware([
        'permission:service-requests.cancel',
        'can:cancel,serviceRequest',
    ]);

    // عروض المهندس.
    Route::get('/my/offers', [OfferController::class, 'myOffers'])
        ->middleware('role:engineer');

    Route::post('/service-requests/{serviceRequest}/offers', [
        OfferController::class,
        'store',
    ])->whereNumber('serviceRequest')->middleware([
        'permission:offers.create',
        'approved',
    ]);

    Route::put('/offers/{offer}', [OfferController::class, 'update'])
        ->whereNumber('offer')
        ->middleware([
            'permission:offers.update',
            'approved',
            'can:update,offer',
        ]);

    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])
        ->whereNumber('offer')
        ->middleware([
            'permission:offers.delete',
            'approved',
            'can:delete,offer',
        ]);

    // العميل يرى عروض طلبه ويقبل أحدها.
    Route::get('/service-requests/{serviceRequest}/offers', [
        OfferController::class,
        'requestOffers',
    ])->whereNumber('serviceRequest')->middleware([
        'role:customer',
        'can:view,serviceRequest',
    ]);

    Route::patch('/offers/{offer}/accept', [
        OfferController::class,
        'accept',
    ])->whereNumber('offer')->middleware([
        'permission:offers.accept',
        'can:accept,offer',
    ]);
});
