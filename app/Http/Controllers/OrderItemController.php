<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    public function index() { return response()->json(OrderItem::with(['orderStore', 'storeProduct'])->get()); }
    public function store(StoreOrderItemRequest $request) { return response()->json(OrderItem::create($request->validated()), 201); }
    public function show($id) { return response()->json(OrderItem::with(['orderStore', 'storeProduct'])->findOrFail($id)); }
    public function update(StoreOrderItemRequest $request, $id) { $item = OrderItem::findOrFail($id); $item->update($request->validated()); return response()->json($item); }
    public function destroy($id) { OrderItem::findOrFail($id)->delete(); return response()->json(['message' => 'Order item deleted successfully']); }
}