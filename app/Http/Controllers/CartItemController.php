<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CartItem::with(['cart', 'storeProduct.masterProduct'])->get());
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $item = CartItem::create($request->validated());
        return response()->json($item, 201);
    }

    public function show($id): JsonResponse
    {
        $item = CartItem::with(['cart', 'storeProduct.masterProduct'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(StoreCartItemRequest $request, $id): JsonResponse
    {
        $item = CartItem::findOrFail($id);
        $item->update($request->validated());
        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        CartItem::findOrFail($id)->delete();
        return response()->json(['message' => 'Item removed from cart successfully']);
    }
}