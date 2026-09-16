<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::with('children')->whereNull('parent_id')->get());
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        return response()->json($category, 201);
    }

    public function show($id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'masterProducts'])->findOrFail($id);
        return response()->json($category);
    }

    public function update(StoreCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());
        return response()->json($category);
    }

    public function destroy($id): JsonResponse
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}