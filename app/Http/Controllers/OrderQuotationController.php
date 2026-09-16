<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderQuotationRequest;
use App\Models\OrderQuotation;
use Illuminate\Http\JsonResponse;

class OrderQuotationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(OrderQuotation::with('orderStore')->paginate(15));
    }

    public function store(StoreOrderQuotationRequest $request): JsonResponse
    {
        $quotation = OrderQuotation::create($request->validated());
        return response()->json($quotation, 201);
    }

    public function show($id): JsonResponse
    {
        $quotation = OrderQuotation::with('orderStore')->findOrFail($id);
        return response()->json($quotation);
    }

    public function update(StoreOrderQuotationRequest $request, $id): JsonResponse
    {
        $quotation = OrderQuotation::findOrFail($id);
        $quotation->update($request->validated());
        return response()->json($quotation);
    }

    public function destroy($id): JsonResponse
    {
        OrderQuotation::findOrFail($id)->delete();
        return response()->json(['message' => 'Quotation deleted successfully']);
    }
}