<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Services\EngineerProfileService;
use App\Services\OfferService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OfferService $offerService,
        private readonly EngineerProfileService $engineerProfileService
    ) {
    }

    public function store(StoreOfferRequest $request,ServiceRequest $serviceRequest) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $offer = $this->offerService->create(
            $engineer,
            $serviceRequest,
            $request->validated()
        );

        return $this->successResponse(
            new OfferResource($offer),
            'Offer submitted successfully.',
            201
        );
    }

    public function myOffers(Request $request)
    {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $offers = $this->offerService
            ->getEngineerOffers($engineer);

        return $this->successResponse(
            OfferResource::collection($offers),
            'Offers retrieved successfully.'
        );
    }

    public function update(UpdateOfferRequest $request,Offer $offer) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $offer = $this->offerService->update(
            $engineer,
            $offer,
            $request->validated()
        );

        return $this->successResponse(
            new OfferResource($offer),
            'Offer updated successfully.'
        );
    }

    public function destroy(Request $request,Offer $offer) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $this->offerService->delete(
            $engineer,
            $offer
        );

        return $this->successResponse(
            null,
            'Offer deleted successfully.'
        );
    }

    public function requestOffers(Request $request,ServiceRequest $serviceRequest) {
        $offers = $this->offerService->getRequestOffers(
            $request->user(),
            $serviceRequest
        );

        return $this->successResponse(
            OfferResource::collection($offers),
            'Offers retrieved successfully.'
        );
    }

    public function accept(Request $request,Offer $offer) {
        $offer = $this->offerService->accept(
            $request->user(),
            $offer
        );

        return $this->successResponse(
            new OfferResource($offer),
            'Offer accepted successfully.'
        );
    }
}