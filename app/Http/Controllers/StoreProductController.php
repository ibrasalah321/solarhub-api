<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreProductRequest;
use App\Models\StoreProduct;
use Illuminate\Http\JsonResponse;

class StoreProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StoreProduct::with(['masterProduct', 'store', 'governorate'])->paginate(20));
    }

    public function store(StoreStoreProductRequest $request): JsonResponse
    {
        $product = StoreProduct::create($request->validated());
        return response()->json($product, 201);
    }

    public function show($id): JsonResponse
    {
        $product = StoreProduct::with([
            'masterProduct.specifications',
            'masterProduct.images',
            'store',
            'governorate'
        ])->findOrFail($id);

        return response()->json($product);
    }

    public function update(StoreStoreProductRequest $request, $id): JsonResponse
    {
        $product = StoreProduct::findOrFail($id);
        $product->update($request->validated());
        return response()->json($product);
    }

    public function destroy($id): JsonResponse
    {
        $product = StoreProduct::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Store product deleted successfully']);
    }
}