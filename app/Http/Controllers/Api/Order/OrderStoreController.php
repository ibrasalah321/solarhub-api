<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStoreStatusRequest;
use App\Http\Resources\Order\OrderStoreResource;
use App\Models\OrderStore;
use App\Services\Notification\DomainNotificationDispatcher;
use App\Services\Order\OrderStoreService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderStoreController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OrderStoreService $orderStoreService,
        private readonly DomainNotificationDispatcher $notifications
    ) {}

    /**
     * Display the authenticated store owner's order branches,
     * optionally filtered by ?status=pending|accepted|shipped|delivered|completed|rejected|cancelled.
     */
    public function index(Request $request)
    {
        $orderStores = $this->orderStoreService->getMyStoreOrders(
            $request->user(),
            $request->query('status')
        );

        return $this->successResponse(
            OrderStoreResource::collection($orderStores),
            'Store orders retrieved successfully.'
        );
    }

    /**
     * Display a single order-store branch.
     */
    public function show(OrderStore $orderStore)
    {
        return $this->successResponse(
            new OrderStoreResource(
                $orderStore->load(['order.customer', 'items.storeProduct.masterProduct', 'payout'])
            ),
            'Store order retrieved successfully.'
        );
    }

    /**
     * Update the status of an order-store branch (store owner action).
     */
    public function updateStatus(UpdateOrderStoreStatusRequest $request, OrderStore $orderStore)
    {
        $orderStore = $this->orderStoreService->updateStatus(
            $request->user(),
            $orderStore,
            $request->validated('status'),
            $request->validated('notes')
        );
        $orderStore->loadMissing('order.customer', 'store');

        $this->notifications->orderStatusChanged(
            $request->user(),
            $orderStore
        );

        return $this->successResponse(
            new OrderStoreResource($orderStore),
            'Store order status updated successfully.'
        );
    }

    /**
     * Customer confirms receipt of a delivered order-store branch.
     */
    public function confirmDelivery(Request $request, OrderStore $orderStore)
    {
        $orderStore = $this->orderStoreService->confirmDelivery(
            $request->user(),
            $orderStore
        );
        $orderStore->loadMissing('order.customer', 'store.user');

        $this->notifications->deliveryConfirmed(
            $request->user(),
            $orderStore
        );

        return $this->successResponse(
            new OrderStoreResource($orderStore),
            'Delivery confirmed successfully.'
        );
    }
}
