<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreApprovalRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\Store\StoreResource;
use App\Models\Store;
use App\Services\Store\StoreService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StoreService $storeService
    ) {
    }

    /**
     * Display all approved stores (public storefront listing).
     */
    public function index()
    {
        $stores = Store::query()
            ->where('approval_status', 'approved')
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings as approved_ratings_count' => fn ($q) => $q->where('is_approved', true)])
            ->paginate(15);

        return $this->successResponse(
            StoreResource::collection($stores),
            'Stores retrieved successfully.'
        );
    }

    /**
     * Display a single store.
     */
    public function show(Store $store)
    {
        $store->loadAvg('ratings', 'rating')
            ->loadCount(['ratings as approved_ratings_count' => fn ($q) => $q->where('is_approved', true)]);

        return $this->successResponse(
            new StoreResource($store),
            'Store retrieved successfully.'
        );
    }

    /**
     * Submit a store application for the authenticated user.
     */
    public function store(StoreStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('commercial_file')) {
            $data['commercial_file'] = $request->file('commercial_file');
        }

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo');
        }

        $store = $this->storeService->applyAsStore($request->user(), $data);

        return $this->successResponse(
            new StoreResource($store),
            'Store application submitted successfully and is pending approval.',
            201
        );
    }

    /**
     * Update the authenticated store owner's own store profile.
     */
    public function update(UpdateStoreRequest $request, Store $store)
    {
        $data = $request->validated();

        if ($request->hasFile('commercial_file')) {
            $data['commercial_file'] = $request->file('commercial_file');
        }

        if ($request->hasFile('company_logo')) {
            $data['company_logo'] = $request->file('company_logo');
        }

        $store = $this->storeService->updateMyStore($request->user(), $store, $data);

        return $this->successResponse(
            new StoreResource($store),
            'Store updated successfully.'
        );
    }

    /**
     * Approve or reject a store application.
     *
     * NOTE: intended for platform administrators. auth:sanctum only for now — see module notes.
     */
    public function updateApproval(UpdateStoreApprovalRequest $request, Store $store)
    {
        $store = $this->storeService->updateApproval(
            $store,
            $request->validated('approval_status'),
            $request->validated('rejection_reason')
        );

        return $this->successResponse(
            new StoreResource($store),
            'Store approval status updated successfully.'
        );
    }
}
