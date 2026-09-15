<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;

class OrderController extends Controller
{
    public function index() { return response()->json(Order::with(['customer', 'governorate', 'orderStores'])->get()); }
    public function store(StoreOrderRequest $request) { return response()->json(Order::create($request->validated()), 201); }
    public function show($id) { return response()->json(Order::with(['customer', 'governorate', 'orderStores.items'])->findOrFail($id)); }
    public function update(StoreOrderRequest $request, $id) { $order = Order::findOrFail($id); $order->update($request->validated()); return response()->json($order); }
    public function destroy($id) { Order::findOrFail($id)->delete(); return response()->json(['message' => 'Order deleted successfully']); }
}