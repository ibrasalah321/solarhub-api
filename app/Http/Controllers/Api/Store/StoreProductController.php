<?php

namespace App\Http\Controllers\Api\Store;

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

    public function show(StoreProduct $storeProduct)
    {
        // هذا مسار عام؛ لا يعرض منتجًا غير نشط أو متجرًا غير معتمد.
        abort_unless(
            $storeProduct->status === 'active'
                && $storeProduct->store?->approval_status === 'approved',
            404
        );

        return $this->successResponse(
            new StoreProductResource(
                $storeProduct->load([
                    'masterProduct.specifications',
                    'masterProduct.images',
                    'store',
                    'governorate',
                ])
            ),
            'Product retrieved successfully.'
        );
    }

    public function myListings(Request $request)
    {
        $products = $this->storeProductService->getMyListings($request->user());

        return $this->successResponse(
            StoreProductResource::collection($products),
            'Your product listings retrieved successfully.'
        );
    }

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

    public function update(
        UpdateStoreProductRequest $request,
        StoreProduct $storeProduct
    ) {
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

    public function destroy(Request $request, StoreProduct $storeProduct)
    {
        $this->storeProductService->deleteListing(
            $request->user(),
            $storeProduct
        );

        return $this->successResponse(
            null,
            'Product listing deleted successfully.'
        );
    }
}
