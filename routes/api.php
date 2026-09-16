<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\ServiceTypeController;

use App\Http\Controllers\Api\EngineerProfileController;
use App\Http\Controllers\Api\EngineerCertificateController;
use App\Http\Controllers\Api\PortfolioItemController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\EngineerRatingController;


Route::get('/specializations', [SpecializationController::class,'index',]);
Route::get('/service-types', [ServiceTypeController::class,'index',]);
Route::get('/engineers/{engineer}/portfolio-items',[PortfolioItemController::class, 'publicIndex']);

Route::get('/engineers', [EngineerProfileController::class,'index',]);

Route::get('/engineers/{engineer}', [EngineerProfileController::class,'show',]);

Route::get('/engineers/{engineer}/ratings',[EngineerRatingController::class, 'engineerRatings']);


// ------------------AUTH ROUTES ----------------


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/engineer/profile', [EngineerProfileController::class,'myProfile',]);

    Route::put('/engineer/profile', [EngineerProfileController::class,'update',]);

    Route::get('/engineer/certificates', [EngineerCertificateController::class,'index',]);

    Route::post('/engineer/certificates', [EngineerCertificateController::class,'store',]);

    Route::delete('/engineer/certificates/{certificate}', [EngineerCertificateController::class,'destroy',]);
    Route::get('/engineer/portfolio-items', [PortfolioItemController::class,'index',]);

    Route::post('/engineer/portfolio-items', [PortfolioItemController::class,'store',]);

    Route::put('/engineer/portfolio-items/{portfolioItem}', [PortfolioItemController::class,'update',]);

    Route::delete('/engineer/portfolio-items/{portfolioItem}', [PortfolioItemController::class,'destroy',]);

    Route::get('/service-requests/my', [ServiceRequestController::class,'myRequests',]);

    Route::get('/service-requests/open', [ServiceRequestController::class,'openRequests',]);

    Route::post('/service-requests', [ServiceRequestController::class,'store',]);

    Route::put('/service-requests/{serviceRequest}', [ServiceRequestController::class,'update',]);

    Route::patch('/service-requests/{serviceRequest}/cancel', [ServiceRequestController::class,'cancel',]);

    Route::post('/service-requests/{serviceRequest}/offers',[OfferController::class, 'store']);

    Route::get('/engineer/offers',[OfferController::class, 'myOffers']);

    Route::put('/offers/{offer}',[OfferController::class, 'update']);

    Route::delete('/offers/{offer}',[OfferController::class, 'destroy']);

    Route::get('/service-requests/{serviceRequest}/offers',[OfferController::class, 'requestOffers']);

    Route::patch('/offers/{offer}/accept',[OfferController::class, 'accept']);

    Route::post('/service-requests/{serviceRequest}/rating',[EngineerRatingController::class, 'store']);

    Route::put('/engineer-ratings/{rating}',[EngineerRatingController::class, 'update']);

    Route::delete('/engineer-ratings/{rating}',[EngineerRatingController::class, 'destroy']);
});
