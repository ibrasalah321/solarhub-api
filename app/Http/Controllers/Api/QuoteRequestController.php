<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\RespondQuoteRequestRequest;
use App\Http\Requests\Order\StoreQuoteRequestRequest;
use App\Http\Resources\Order\QuoteRequestResource;
use App\Models\QuoteRequest;
use App\Services\Order\QuoteRequestService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly QuoteRequestService $quoteRequestService
    ) {
    }

    /**
     * Display the authenticated customer's quote requests.
     */
    public function myRequests(Request $request)
    {
        $quoteRequests = $this->quoteRequestService->getMyQuoteRequests($request->user());

        return $this->successResponse(
            QuoteRequestResource::collection($quoteRequests),
            'Quote requests retrieved successfully.'
        );
    }

    /**
     * Display quote requests addressed to the authenticated store owner.
     */
    public function myStoreRequests(Request $request)
    {
        $quoteRequests = $this->quoteRequestService->getMyStoreQuoteRequests(
            $request->user(),
            $request->query('status')
        );

        return $this->successResponse(
            QuoteRequestResource::collection($quoteRequests),
            'Store quote requests retrieved successfully.'
        );
    }

    /**
     * Submit a new quote request for a store product.
     */
    public function store(StoreQuoteRequestRequest $request)
    {
        $quoteRequest = $this->quoteRequestService->createQuoteRequest(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new QuoteRequestResource($quoteRequest),
            'Quote request submitted successfully.',
            201
        );
    }

    /**
     * Store owner responds to a pending quote request.
     */
    public function respond(RespondQuoteRequestRequest $request, QuoteRequest $quoteRequest)
    {
        $quoteRequest = $this->quoteRequestService->respondToQuote(
            $request->user(),
            $quoteRequest,
            $request->validated()
        );

        return $this->successResponse(
            new QuoteRequestResource($quoteRequest),
            'Quote response submitted successfully.'
        );
    }

    /**
     * Customer accepts the store's offer.
     */
    public function accept(Request $request, QuoteRequest $quoteRequest)
    {
        $quoteRequest = $this->quoteRequestService->acceptQuote(
            $request->user(),
            $quoteRequest
        );

        return $this->successResponse(
            new QuoteRequestResource($quoteRequest),
            'Quote request accepted successfully.'
        );
    }

    /**
     * Customer rejects or cancels the quote request.
     */
    public function reject(Request $request, QuoteRequest $quoteRequest)
    {
        $quoteRequest = $this->quoteRequestService->rejectQuote(
            $request->user(),
            $quoteRequest
        );

        return $this->successResponse(
            new QuoteRequestResource($quoteRequest),
            'Quote request rejected successfully.'
        );
    }
}
