<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderStoreRequest;
use App\Models\OrderStore;
use Illuminate\Http\JsonResponse;

class OrderStoreController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(OrderStore::with(['order', 'store'])->paginate(15));
    }

    public function store(StoreOrderStoreRequest $request): JsonResponse
    {
        $orderStore = OrderStore::create($request->validated());
        return response()->json($orderStore, 201);
    }

    public function show($id): JsonResponse
    {
        $orderStore = OrderStore::with(['order', 'store', 'items.storeProduct', 'payout', 'rating'])->findOrFail($id);
        return response()->json($orderStore);
    }

    public function update(StoreOrderStoreRequest $request, $id): JsonResponse
    {
        $orderStore = OrderStore::findOrFail($id);
        $orderStore->update($request->validated());
        return response()->json($orderStore);
    }

    public function destroy($id): JsonResponse
    {
        $orderStore = OrderStore::findOrFail($id);
        $orderStore->delete();
        return response()->json(['message' => 'Order store record deleted successfully']);
    }
}