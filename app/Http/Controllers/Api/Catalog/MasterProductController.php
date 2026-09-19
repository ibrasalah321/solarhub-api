<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreMasterProductRequest;
use App\Http\Requests\Catalog\UpdateMasterProductRequest;
use App\Http\Resources\Catalog\MasterProductResource;
use App\Models\MasterProduct;
use App\Services\SupabaseStorageService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class MasterProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function index(Request $request)
    {
        $products = MasterProduct::query()
            ->with(['category', 'brand', 'images'])
            ->withCount('storeProducts')
            ->where('is_active', true)
            ->when($request->query('category_id'), fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->query('brand_id'), fn ($q, $brandId) => $q->where('brand_id', $brandId))
            ->when($request->query('search'), fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->paginate(20);

        return $this->successResponse(
            MasterProductResource::collection($products),
            'Products retrieved successfully.'
        );
    }

    public function show(MasterProduct $masterProduct)
    {
        $masterProduct->load(['category', 'brand', 'specifications', 'images'])
            ->loadCount('storeProducts');

        return $this->successResponse(
            new MasterProductResource($masterProduct),
            'Product retrieved successfully.'
        );
    }

    public function store(StoreMasterProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('datasheet')) {
            $data['datasheet_file'] = $this->storageService->uploadPublic(
                $request->file('datasheet'),
                'products/datasheets'
            );
        }

        unset($data['datasheet']);

        $product = MasterProduct::create($data);

        return $this->successResponse(
            new MasterProductResource($product->load(['category', 'brand'])),
            'Product created successfully.',
            201
        );
    }

    public function update(UpdateMasterProductRequest $request, MasterProduct $masterProduct)
    {
        $data = $request->validated();

        if ($request->hasFile('datasheet')) {
            $this->storageService->deletePublic($masterProduct->datasheet_file);
            $data['datasheet_file'] = $this->storageService->uploadPublic(
                $request->file('datasheet'),
                'products/datasheets'
            );
        }

        unset($data['datasheet']);

        $masterProduct->update($data);

        return $this->successResponse(
            new MasterProductResource($masterProduct->load(['category', 'brand'])),
            'Product updated successfully.'
        );
    }

    public function destroy(MasterProduct $masterProduct)
    {
        abort_if(
            $masterProduct->storeProducts()->exists(),
            409,
            'This product is currently listed by one or more stores and cannot be deleted.'
        );

        $this->storageService->deletePublic($masterProduct->datasheet_file);

        foreach ($masterProduct->images as $image) {
            $this->storageService->deletePublic($image->image_path);
        }

        $masterProduct->delete();

        return $this->successResponse(null, 'Product deleted successfully.');
    }
}
