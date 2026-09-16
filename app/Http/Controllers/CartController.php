<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Cart::with(['user', 'items.storeProduct.masterProduct'])->get());
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        $cart = Cart::create($request->validated());
        return response()->json($cart, 201);
    }

    public function show($id): JsonResponse
    {
        $cart = Cart::with(['user', 'items.storeProduct.masterProduct'])->findOrFail($id);
        return response()->json($cart);
    }

    public function update(StoreCartRequest $request, $id): JsonResponse
    {
        $cart = Cart::findOrFail($id);
        $cart->update($request->validated());
        return response()->json($cart);
    }

    public function destroy($id): JsonResponse
    {
        Cart::findOrFail($id)->delete();
        return response()->json(['message' => 'Cart deleted successfully']);
    }
}