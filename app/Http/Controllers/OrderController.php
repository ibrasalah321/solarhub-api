<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Order::with(['customer', 'governorate'])->paginate(15));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::create($request->validated());
        return response()->json($order, 201);
    }

    public function show($id): JsonResponse
    {
        $order = Order::with(['customer', 'governorate', 'orderStores.items.storeProduct.masterProduct', 'payments'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(StoreOrderRequest $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->update($request->validated());
        return response()->json($order);
    }

    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}