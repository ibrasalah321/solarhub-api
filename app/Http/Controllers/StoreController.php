<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Store::with('user')->paginate(15));
    }

    public function store(StoreStoreRequest $request): JsonResponse
    {
        $store = Store::create($request->validated());
        return response()->json($store, 201);
    }

    public function show($id): JsonResponse
    {
        $store = Store::with(['user', 'storeProducts.masterProduct'])->findOrFail($id);
        return response()->json($store);
    }

    public function update(StoreStoreRequest $request, $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $store->update($request->validated());
        return response()->json($store);
    }

    public function destroy($id): JsonResponse
    {
        Store::findOrFail($id)->delete();
        return response()->json(['message' => 'Store deleted successfully']);
    }
}