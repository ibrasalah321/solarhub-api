<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    /**
     * Display the authenticated customer's orders.
     */
    public function index(Request $request)
    {
        $orders = $this->orderService->getMyOrders($request->user());

        return $this->successResponse(
            OrderResource::collection($orders),
            'Orders retrieved successfully.'
        );
    }

    /**
     * Display a single order with full details.
     */
    public function show(Request $request, Order $order)
    {
        abort_unless(
            $order->customer_id === $request->user()->id,
            403,
            'You are not allowed to view this order.'
        );

        return $this->successResponse(
            new OrderResource($this->orderService->getOrderDetails($order)),
            'Order retrieved successfully.'
        );
    }

    /**
     * Checkout: convert the authenticated customer's cart into an order.
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->createFromCart(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new OrderResource($order),
            'Order placed successfully.',
            201
        );
    }

    /**
     * Cancel an order that has not started processing yet.
     */
    public function cancel(Request $request, Order $order)
    {
        $order = $this->orderService->cancelOrder($request->user(), $order);

        return $this->successResponse(
            new OrderResource($order),
            'Order cancelled successfully.'
        );
    }
}
