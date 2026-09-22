<?php

namespace App\Http\Controllers\Api\Rating;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rating\StoreStoreRatingRequest;
use App\Http\Requests\Rating\UpdateStoreRatingRequest;

use App\Http\Resources\Rating\StoreRatingResource;
use App\Models\Store;
use App\Models\StoreRating;
use App\Services\Rating\StoreRatingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StoreRatingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StoreRatingService $storeRatingService
    ) {
    }

    /**
     * Display approved ratings for a specific store (public).
     */
    public function storeRatings(Store $store)
    {
        $ratings = $store->ratings()
            ->where('is_approved', true)
            ->with(['customer'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            StoreRatingResource::collection($ratings),
            'Store ratings retrieved successfully.'
        );
    }

    /**
     * Submit a new rating for a completed order.
     */
    public function store(StoreStoreRatingRequest $request)
    {
        $rating = $this->storeRatingService->create(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new StoreRatingResource($rating),
            'Rating submitted successfully.',
            201
        );
    }

    /**
     * Update an existing rating.
     */
    public function update(UpdateStoreRatingRequest $request, StoreRating $storeRating)
    {
        $rating = $this->storeRatingService->update(
            $request->user(),
            $storeRating,
            $request->validated()
        );

        return $this->successResponse(
            new StoreRatingResource($rating),
            'Rating updated successfully.'
        );
    }

    /**
     * Delete a rating.
     */
    public function destroy(Request $request, StoreRating $storeRating)
    {
        $this->storeRatingService->delete($request->user(), $storeRating);

        return $this->successResponse(null, 'Rating deleted successfully.');
    }
}
