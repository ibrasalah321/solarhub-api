<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engineer\StorePortfolioItemRequest;
use App\Http\Requests\Engineer\UpdatePortfolioItemRequest;
use App\Http\Resources\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Services\EngineerProfileService;
use App\Services\PortfolioItemService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class PortfolioItemController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PortfolioItemService $portfolioService,
        private readonly EngineerProfileService $engineerProfileService
    ) {
    }

    public function index(Request $request)
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

        $portfolioItems = $this->portfolioService
            ->getEngineerPortfolio($engineer);

        return $this->successResponse(
            PortfolioItemResource::collection($portfolioItems),
            'Portfolio items retrieved successfully.'
        );
    }

    public function store(StorePortfolioItemRequest $request)
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

        $portfolioItem = $this->portfolioService->create(
            $engineer,
            $request->validated()
        );

        return $this->successResponse(
            new PortfolioItemResource($portfolioItem),
            'Portfolio item created successfully.',
            201
        );
    }

    public function update(UpdatePortfolioItemRequest $request,PortfolioItem $portfolioItem) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $portfolioItem = $this->portfolioService->update(
            $engineer,
            $portfolioItem,
            $request->validated()
        );

        return $this->successResponse(
            new PortfolioItemResource($portfolioItem),
            'Portfolio item updated successfully.'
        );
    }

    public function destroy(Request $request,PortfolioItem $portfolioItem) {
        $engineer = $this->engineerProfileService
            ->getMyProfile($request->user());

        if (!$engineer) {
            return $this->errorResponse(
                'Engineer profile not found.',
                null,
                404
            );
        }

        $this->portfolioService->delete(
            $engineer,
            $portfolioItem
        );

        return $this->successResponse(
            null,
            'Portfolio item deleted successfully.'
        );
    }

    public function publicIndex(Request $request,int $engineer) {
        $perPage = min(
            max((int) $request->query('per_page', 10), 1),
            50
        );

        $portfolioItems = $this->portfolioService
            ->getPublicPortfolio($engineer, $perPage);

        return $this->successResponse(
            PortfolioItemResource::collection($portfolioItems),
            'Portfolio items retrieved successfully.'
        );
    }
}