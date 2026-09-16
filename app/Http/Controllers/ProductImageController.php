<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductImageRequest;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;

class ProductImageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ProductImage::with('masterProduct')->get());
    }

    public function store(StoreProductImageRequest $request): JsonResponse
    {
        $image = ProductImage::create($request->validated());
        return response()->json($image, 201);
    }

    public function show($id): JsonResponse
    {
        $image = ProductImage::with('masterProduct')->findOrFail($id);
        return response()->json($image);
    }

    public function update(StoreProductImageRequest $request, $id): JsonResponse
    {
        $image = ProductImage::findOrFail($id);
        $image->update($request->validated());
        return response()->json($image);
    }

    public function destroy($id): JsonResponse
    {
        ProductImage::findOrFail($id)->delete();
        return response()->json(['message' => 'Product image removed successfully']);
    }
}