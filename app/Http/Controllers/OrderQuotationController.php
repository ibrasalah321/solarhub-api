<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderQuotationRequest;
use App\Models\OrderQuotation;

class OrderQuotationController extends Controller
{
    public function index() { return response()->json(OrderQuotation::with('orderStore')->get()); }
    public function store(StoreOrderQuotationRequest $request) { return response()->json(OrderQuotation::create($request->validated()), 201); }
    public function show($id) { return response()->json(OrderQuotation::with('orderStore')->findOrFail($id)); }
    public function update(StoreOrderQuotationRequest $request, $id) { $quotation = OrderQuotation::findOrFail($id); $quotation->update($request->validated()); return response()->json($quotation); }
    public function destroy($id) { OrderQuotation::findOrFail($id)->delete(); return response()->json(['message' => 'Order quotation deleted successfully']); }
}