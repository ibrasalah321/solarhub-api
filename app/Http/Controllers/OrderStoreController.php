<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderStoreRequest;
use App\Models\OrderStore;

class OrderStoreController extends Controller
{
    public function index() { return response()->json(OrderStore::with(['order', 'store', 'items', 'rating'])->get()); }
    public function store(StoreOrderStoreRequest $request) { return response()->json(OrderStore::create($request->validated()), 201); }
    public function show($id) { return response()->json(OrderStore::with(['order', 'store', 'items', 'rating'])->findOrFail($id)); }
    public function update(StoreOrderStoreRequest $request, $id) { $orderStore = OrderStore::findOrFail($id); $orderStore->update($request->validated()); return response()->json($orderStore); }
    public function destroy($id) { OrderStore::findOrFail($id)->delete(); return response()->json(['message' => 'Order store deleted successfully']); }
}