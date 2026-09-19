<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StoreOrderPaymentRequest;
use App\Http\Requests\Payment\UpdateOrderPaymentRequest;
use App\Http\Resources\Payment\OrderPaymentResource;
use App\Models\OrderPayment;
use App\Services\Payment\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderPaymentController extends Controller
{
    public function __construct(
        protected OrderPaymentService $paymentService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $payments = OrderPayment::with('walletProvider')->latest()->paginate(20);
        return OrderPaymentResource::collection($payments);
    }

    public function store(StoreOrderPaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->submitPayment(
            $request->validated(),
            $request->file('receipt_image')
        );

        return (new OrderPaymentResource($payment->load('walletProvider')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): OrderPaymentResource
    {
        $payment = OrderPayment::with(['walletProvider', 'order'])->findOrFail($id);
        return new OrderPaymentResource($payment);
    }

    public function update(UpdateOrderPaymentRequest $request, int $id): OrderPaymentResource
    {
        $payment = OrderPayment::findOrFail($id);
        $updated = $this->paymentService->verifyPayment(
            $payment,
            $request->user()->id,
            $request->validated('status'),
            $request->validated('notes')
        );

        return new OrderPaymentResource($updated);
    }
}