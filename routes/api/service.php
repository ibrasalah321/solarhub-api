<?php

use App\Http\Controllers\Api\ServiceRequest\OfferController;
use App\Http\Controllers\Api\ServiceRequest\ServiceRequestController;
use App\Http\Controllers\Api\ServiceRequest\ServiceTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/service-types', [ServiceTypeController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // طلبات العميل.
    Route::get('/my/service-requests', [
        ServiceRequestController::class,
        'myRequests',
    ])->middleware('role:customer');

    Route::post('/service-requests', [
    ServiceRequestController::class,
    'store',
])->middleware('permission:service-requests.create');

    Route::put('/service-requests/{serviceRequest}', [
        ServiceRequestController::class,
        'update',
    ])->middleware([
        'permission:service-requests.update',
        'can:update,serviceRequest',
    ]);

    Route::patch('/service-requests/{serviceRequest}/cancel', [
        ServiceRequestController::class,
        'cancel',
    ])->middleware([
        'permission:service-requests.cancel',
        'can:cancel,serviceRequest',
    ]);

    // الطلبات المفتوحة للمهندس المعتمد.
    Route::get('/service-requests/open', [
        ServiceRequestController::class,
        'openRequests',
    ])->middleware([
        'permission:service-requests.view-open',
        'approved',
    ]);

    // عروض المهندس.
    Route::get('/my/offers', [OfferController::class, 'myOffers'])
        ->middleware('role:engineer');

    Route::post('/service-requests/{serviceRequest}/offers', [
        OfferController::class,
        'store',
    ])->middleware([
        'permission:offers.create',
        'approved',
    ]);

    Route::put('/offers/{offer}', [OfferController::class, 'update'])
        ->middleware([
            'permission:offers.update',
            'approved',
            'can:update,offer',
        ]);

    Route::delete('/offers/{offer}', [OfferController::class, 'destroy'])
        ->middleware([
            'permission:offers.delete',
            'approved',
            'can:delete,offer',
        ]);

    // العميل يرى عروض طلبه ويقبل أحدها.
    Route::get('/service-requests/{serviceRequest}/offers', [
        OfferController::class,
        'requestOffers',
    ])->middleware([
        'role:customer',
        'can:view,serviceRequest',
    ]);

    Route::patch('/offers/{offer}/accept', [
        OfferController::class,
        'accept',
    ])->middleware([
        'permission:offers.accept',
        'can:accept,offer',
    ]);
});
