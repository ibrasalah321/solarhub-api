<?php

namespace App\Http\Controllers\Api\Rating;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rating\StoreEngineerRatingRequest;
use App\Http\Requests\Rating\UpdateEngineerRatingRequest;
use App\Http\Resources\Rating\EngineerRatingResource;
use App\Models\EngineerProfile;
use App\Models\EngineerRating;
use App\Models\ServiceRequest;
use App\Services\Rating\EngineerRatingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class EngineerRatingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EngineerRatingService $ratingService,
        private readonly \App\Services\Notification\DomainNotificationDispatcher $notifications
    ) {}

    /**
     * Display all engineer ratings for administration.
     */
    public function index(Request $request)
    {
        $ratings = EngineerRating::query()
            ->with(['customer', 'engineer.user'])
            ->latest()
            ->paginate((int) $request->input('per_page', 100));

        return $this->successResponse(
            EngineerRatingResource::collection($ratings),
            'Engineer ratings retrieved successfully.'
        );
    }

    /**
     * Toggle approval status of an engineer rating.
     */
    public function toggleApproval(EngineerRating $rating)
    {
        $rating->update([
            'is_approved' => ! $rating->is_approved,
        ]);

        return $this->successResponse(
            new EngineerRatingResource($rating->load(['customer', 'engineer.user'])),
            'Rating approval status toggled successfully.'
        );
    }

    /**
     * POST /engineer-ratings — العميل يقيّم المهندس مرة واحدة بعد اكتمال طلب الخدمة.
     * (الملكية والاكتمال وعدم التكرار تُفحص في EngineerRatingService)
     */
    public function store(StoreEngineerRatingRequest $request)
    {
        $data = $request->validated();
        $serviceRequest = ServiceRequest::query()->findOrFail($data['service_request_id']);

        $rating = $this->ratingService->create(
            $request->user(),
            $serviceRequest,
            $data
        );

        $this->notifications->engineerRated($request->user(), $rating);

        return $this->successResponse(
            new EngineerRatingResource($rating),
            'Engineer rated successfully.',
            201
        );
    }

    public function update(UpdateEngineerRatingRequest $request, EngineerRating $rating)
    {
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

    public function destroy(Request $request, EngineerRating $rating)
    {
        $this->ratingService->delete(
            $request->user(),
            $rating
        );

        return $this->successResponse(
            null,
            'Rating deleted successfully.'
        );
    }

    public function engineerRatings(EngineerProfile $engineer)
    {
        $ratings = $this->ratingService
            ->getEngineerRatings($engineer);

        return $this->successResponse(
            EngineerRatingResource::collection($ratings),
            'Engineer ratings retrieved successfully.'
        );
    }

    public function myRatings(Request $request)
    {
        $engineer = app(\App\Services\Engineer\EngineerProfileService::class)
            ->getMyProfile($request->user());

        if (! $engineer) {
            return $this->errorResponse('Engineer profile not found.', null, 404);
        }

        $ratings = $engineer->ratings()
            ->whereRaw('is_approved IS TRUE')
            ->with('customer')
            ->latest()
            ->paginate((int) $request->input('per_page', 100));

        return $this->successResponse(
            EngineerRatingResource::collection($ratings),
            'Engineer ratings retrieved successfully.'
        );
    }
}
