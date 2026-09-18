<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreStoreProductRequest;
use App\Http\Requests\Store\UpdateStoreProductRequest;
use App\Http\Resources\Store\StoreProductResource;
use App\Models\StoreProduct;
use App\Services\Store\StoreProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StoreProductService $storeProductService
    ) {
    }

    /**
     * Public storefront browsing, filterable by governorate_id, category_id, store_id.
     */
    public function index(Request $request)
    {
        $products = $this->storeProductService->browse($request->only([
            'governorate_id',
            'category_id',
            'store_id',
        ]));

        return $this->successResponse(
            StoreProductResource::collection($products),
            'Products retrieved successfully.'
        );
    }

    /**
     * Display a single listing.
     */
    public function show(StoreProduct $storeProduct)
    {
        return $this->successResponse(
            new StoreProductResource(
                $storeProduct->load(['masterProduct.specifications', 'masterProduct.images', 'store', 'governorate'])
            ),
            'Product retrieved successfully.'
        );
    }

    /**
     * List the authenticated store owner's own listings.
     */
    public function myListings(Request $request)
    {
        $products = $this->storeProductService->getMyListings($request->user());

        return $this->successResponse(
            StoreProductResource::collection($products),
            'Your product listings retrieved successfully.'
        );
    }

    /**
     * Create a new listing.
     */
    public function store(StoreStoreProductRequest $request)
    {
        $product = $this->storeProductService->createListing(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new StoreProductResource($product),
            'Product listed successfully.',
            201
        );
    }

    /**
     * Update an existing listing.
     */
    public function update(UpdateStoreProductRequest $request, StoreProduct $storeProduct)
    {
        $product = $this->storeProductService->updateListing(
            $request->user(),
            $storeProduct,
            $request->validated()
        );

        return $this->successResponse(
            new StoreProductResource($product),
            'Product updated successfully.'
        );
    }

    /**
     * Delete a listing.
     */
    public function destroy(Request $request, StoreProduct $storeProduct)
    {
        $this->storeProductService->deleteListing($request->user(), $storeProduct);

        return $this->successResponse(null, 'Product listing deleted successfully.');
    }
}
