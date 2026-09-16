<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductSpecificationRequest;
use App\Models\ProductSpecification;
use Illuminate\Http\JsonResponse;

class ProductSpecificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ProductSpecification::with('masterProduct')->get());
    }

    public function store(StoreProductSpecificationRequest $request): JsonResponse
    {
        $spec = ProductSpecification::create($request->validated());
        return response()->json($spec, 201);
    }

    public function show($id): JsonResponse
    {
        $spec = ProductSpecification::with('masterProduct')->findOrFail($id);
        return response()->json($spec);
    }

    public function update(StoreProductSpecificationRequest $request, $id): JsonResponse
    {
        $spec = ProductSpecification::findOrFail($id);
        $spec->update($request->validated());
        return response()->json($spec);
    }

    public function destroy($id): JsonResponse
    {
        ProductSpecification::findOrFail($id)->delete();
        return response()->json(['message' => 'Product specification removed successfully']);
    }
}