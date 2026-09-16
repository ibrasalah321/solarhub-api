<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMasterProductRequest;
use App\Models\MasterProduct;
use Illuminate\Http\JsonResponse;

class MasterProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MasterProduct::with(['category', 'brand', 'specifications', 'images'])->paginate(20));
    }

    public function store(StoreMasterProductRequest $request): JsonResponse
    {
        $product = MasterProduct::create($request->validated());
        return response()->json($product, 201);
    }

    public function show($id): JsonResponse
    {
        $product = MasterProduct::with(['category', 'brand', 'specifications', 'images', 'storeProducts.store'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(StoreMasterProductRequest $request, $id): JsonResponse
    {
        $product = MasterProduct::findOrFail($id);
        $product->update($request->validated());
        return response()->json($product);
    }

    public function destroy($id): JsonResponse
    {
        MasterProduct::findOrFail($id)->delete();
        return response()->json(['message' => 'Master product deleted successfully']);
    }
}