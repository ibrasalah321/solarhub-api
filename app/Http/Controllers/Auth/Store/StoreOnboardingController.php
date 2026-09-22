<?php

namespace App\Http\Controllers\Auth\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Store\StoreStoreOnboardingRequest;
use App\Http\Resources\Auth\Store\StoreOnboardingResource;
use App\Services\Auth\Store\StoreOnboardingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StoreOnboardingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StoreOnboardingService $storeOnboardingService
    ) {
    }

    public function store(
        StoreStoreOnboardingRequest $request
    ) {
        $store = $this->storeOnboardingService->store(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new StoreOnboardingResource($store),
            'Store onboarding submitted successfully.',
            201
        );
    }

    public function status(Request $request)
    {
        $store = $this->storeOnboardingService->status(
            $request->user()
        );

        if (! $store) {
            return $this->successResponse(
                null,
                'Store onboarding has not been submitted yet.'
            );
        }

        return $this->successResponse(
            new StoreOnboardingResource($store),
            'Store onboarding status retrieved successfully.'
        );
    }
}