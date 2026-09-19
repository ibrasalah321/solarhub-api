<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreBrandRequest;
use App\Http\Requests\Catalog\UpdateBrandRequest;
use App\Http\Resources\Catalog\BrandResource;
use App\Models\Brand;
use App\Services\SupabaseStorageService;
use App\Traits\ApiResponseTrait;

class BrandController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function index()
    {
        $brands = Brand::query()
            ->withCount('masterProducts')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            BrandResource::collection($brands),
            'Brands retrieved successfully.'
        );
    }

    public function show(Brand $brand)
    {
        return $this->successResponse(
            new BrandResource($brand->loadCount('masterProducts')),
            'Brand retrieved successfully.'
        );
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storageService->uploadPublic($request->file('logo'), 'brands');
        }

        $brand = Brand::create($data);

        return $this->successResponse(
            new BrandResource($brand),
            'Brand created successfully.',
            201
        );
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $this->storageService->deletePublic($brand->logo);
            $data['logo'] = $this->storageService->uploadPublic($request->file('logo'), 'brands');
        }

        $brand->update($data);

        return $this->successResponse(
            new BrandResource($brand),
            'Brand updated successfully.'
        );
    }

    public function destroy(Brand $brand)
    {
        abort_if(
            $brand->masterProducts()->exists(),
            409,
            'This brand has products linked to it and cannot be deleted.'
        );

        $this->storageService->deletePublic($brand->logo);

        $brand->delete();

        return $this->successResponse(null, 'Brand deleted successfully.');
    }
}
