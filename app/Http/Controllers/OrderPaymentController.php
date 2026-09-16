<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderPaymentRequest;
use App\Models\OrderPayment;
use Illuminate\Http\JsonResponse;

class OrderPaymentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(OrderPayment::with(['order', 'walletProvider', 'verifiedBy'])->paginate(15));
    }

    public function store(StoreOrderPaymentRequest $request): JsonResponse
    {
        $payment = OrderPayment::create($request->validated());
        return response()->json($payment, 201);
    }

    public function show($id): JsonResponse
    {
        $payment = OrderPayment::with(['order', 'walletProvider', 'verifiedBy'])->findOrFail($id);
        return response()->json($payment);
    }

    public function update(StoreOrderPaymentRequest $request, $id): JsonResponse
    {
        $payment = OrderPayment::findOrFail($id);
        $payment->update($request->validated());
        return response()->json($payment);
    }

    public function destroy($id): JsonResponse
    {
        $payment = OrderPayment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Payment record deleted successfully']);
    }
}