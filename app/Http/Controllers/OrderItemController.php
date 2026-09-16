<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

class OrderItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(OrderItem::with(['orderStore', 'storeProduct.masterProduct'])->paginate(20));
    }

    public function store(StoreOrderItemRequest $request): JsonResponse
    {
        $item = OrderItem::create($request->validated());
        return response()->json($item, 201);
    }

    public function show($id): JsonResponse
    {
        $item = OrderItem::with(['orderStore', 'storeProduct.masterProduct'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(StoreOrderItemRequest $request, $id): JsonResponse
    {
        $item = OrderItem::findOrFail($id);
        $item->update($request->validated());
        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        OrderItem::findOrFail($id)->delete();
        return response()->json(['message' => 'Order item deleted successfully']);
    }
}