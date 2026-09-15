<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderPaymentRequest;
use App\Models\OrderPayment;

class OrderPaymentController extends Controller
{
    public function index() { return response()->json(OrderPayment::with(['order', 'verifiedBy'])->get()); }
    public function store(StoreOrderPaymentRequest $request) { return response()->json(OrderPayment::create($request->validated()), 201); }
    public function show($id) { return response()->json(OrderPayment::with(['order', 'verifiedBy'])->findOrFail($id)); }
    public function update(StoreOrderPaymentRequest $request, $id) { $payment = OrderPayment::findOrFail($id); $payment->update($request->validated()); return response()->json($payment); }
    public function destroy($id) { OrderPayment::findOrFail($id)->delete(); return response()->json(['message' => 'Order payment deleted successfully']); }
}