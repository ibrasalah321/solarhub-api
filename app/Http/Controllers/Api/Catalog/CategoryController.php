<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Http\Resources\Catalog\CategoryResource;
use App\Models\Category;
use App\Services\SupabaseStorageService;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function index()
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->withCount('masterProducts')
            ->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully.'
        );
    }

    public function show(Category $category)
    {
        return $this->successResponse(
            new CategoryResource(
                $category->load('children')->loadCount('masterProducts')
            ),
            'Category retrieved successfully.'
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storageService->uploadPublic($request->file('icon'), 'categories');
        }

        $category = Category::create($data);

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully.',
            201
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $this->storageService->deletePublic($category->icon);
            $data['icon'] = $this->storageService->uploadPublic($request->file('icon'), 'categories');
        }

        $category->update($data);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully.'
        );
    }

    public function destroy(Category $category)
    {
        abort_if(
            $category->children()->exists() || $category->masterProducts()->exists(),
            409,
            'This category has sub-categories or products linked to it and cannot be deleted.'
        );

        $this->storageService->deletePublic($category->icon);

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully.');
    }
}
