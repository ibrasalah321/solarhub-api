<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StoreOrderPaymentRequest;
use App\Http\Requests\Payment\UpdateOrderPaymentStatusRequest;
use App\Http\Resources\Payment\OrderPaymentResource;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\Payment\OrderPaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OrderPaymentService $orderPaymentService
    ) {
    }

    /**
     * Display all payments for a given order.
     */
    public function index(Order $order)
    {
        $payments = $this->orderPaymentService->getForOrder($order);

        return $this->successResponse(
            OrderPaymentResource::collection($payments),
            'Payments retrieved successfully.'
        );
    }

    /**
     * Display a single payment.
     */
    public function show(OrderPayment $orderPayment)
    {
        return $this->successResponse(
            new OrderPaymentResource($orderPayment->load(['walletProvider', 'verifiedBy'])),
            'Payment retrieved successfully.'
        );
    }

    /**
     * Submit a new payment proof for an order.
     */
    public function store(StoreOrderPaymentRequest $request)
    {
        $payment = $this->orderPaymentService->submitPayment(
            $request->user(),
            $request->validated(),
            $request->file('receipt')
        );

        return $this->successResponse(
            new OrderPaymentResource($payment),
            'Payment submitted successfully and is pending verification.',
            201
        );
    }

    /**
     * Verify or reject a pending payment.
     *
     * NOTE: this endpoint is intended for platform administrators.
     * The current schema has no role/permission system yet, so authorization
     * here is limited to `auth:sanctum`. Add a policy/role gate before going to production.
     */
    public function updateStatus(UpdateOrderPaymentStatusRequest $request, OrderPayment $orderPayment)
    {
        $payment = $request->validated('status') === 'verified'
            ? $this->orderPaymentService->verify($request->user(), $orderPayment)
            : $this->orderPaymentService->reject($request->user(), $orderPayment);

        return $this->successResponse(
            new OrderPaymentResource($payment),
            'Payment status updated successfully.'
        );
    }
}
