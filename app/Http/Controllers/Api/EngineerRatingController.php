<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rating\StoreEngineerRatingRequest;
use App\Http\Requests\Rating\UpdateEngineerRatingRequest;
use App\Http\Resources\EngineerRatingResource;
use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\ServiceRequest;
use App\Services\EngineerRatingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class EngineerRatingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EngineerRatingService $ratingService
    ) {
    }

    public function store(StoreEngineerRatingRequest $request,ServiceRequest $serviceRequest) {
        $rating = $this->ratingService->create(
            $request->user(),
            $serviceRequest,
            $request->validated()
        );

        return $this->successResponse(
            new EngineerRatingResource($rating),
            'Engineer rated successfully.',
            201
        );
    }

    public function update(UpdateEngineerRatingRequest $request,EngineerRating $rating) {
        $rating = $this->ratingService->update(
            $request->user(),
            $rating,
            $request->validated()
        );

        return $this->successResponse(
            new EngineerRatingResource($rating),
            'Rating updated successfully.'
        );
    }

    public function destroy(Request $request,EngineerRating $rating) {
        $this->ratingService->delete(
            $request->user(),
            $rating
        );

        return $this->successResponse(
            null,
            'Rating deleted successfully.'
        );
    }

    public function engineerRatings(EngineerProfile $engineer) {
        $ratings = $this->ratingService
            ->getEngineerRatings($engineer);

        return $this->successResponse(
            EngineerRatingResource::collection($ratings),
            'Engineer ratings retrieved successfully.'
        );
    }
}