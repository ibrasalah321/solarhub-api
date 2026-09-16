<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Brand::with('masterProducts')->get());
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());
        return response()->json($brand, 201);
    }

    public function show($id): JsonResponse
    {
        $brand = Brand::with('masterProducts.specifications')->findOrFail($id);
        return response()->json($brand);
    }

    public function update(StoreBrandRequest $request, $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->update($request->validated());
        return response()->json($brand);
    }

    public function destroy($id): JsonResponse
    {
        Brand::findOrFail($id)->delete();
        return response()->json(['message' => 'Brand deleted successfully']);
    }
}

