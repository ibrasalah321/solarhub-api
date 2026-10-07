<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StoreStorePayoutRequest;
use App\Http\Requests\Payment\UpdateStorePayoutRequest;
use App\Http\Resources\Payment\StorePayoutResource;
use App\Models\StorePayout;
use App\Services\Notification\DomainNotificationDispatcher;
use App\Services\Payment\StorePayoutService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StorePayoutController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StorePayoutService $storePayoutService,
        private readonly DomainNotificationDispatcher $notifications
    ) {}

    /**
     * Display all payouts, optionally filtered by ?status=pending|completed|failed.
     *
     * NOTE: intended for platform administrators. See UpdateStorePayoutRequest note
     * regarding the current absence of a role/permission system.
     */
    public function index(Request $request)
    {
        $payouts = $this->storePayoutService->list($request->query('status'));

        return $this->successResponse(
            StorePayoutResource::collection($payouts),
            'Payouts retrieved successfully.'
        );
    }

    /**
     * Display a single payout.
     */
    public function show(StorePayout $storePayout)
    {
        return $this->successResponse(
            new StorePayoutResource(
                $storePayout->load(['orderStore.store', 'userWallet.walletProvider'])
            ),
            'Payout retrieved successfully.'
        );
    }

    /**
     * Create a payout for a delivered/completed order-store.
     * Commission and net amounts are computed server-side; never trusted from the client.
     */
    public function store(StoreStorePayoutRequest $request)
    {
        $payout = $this->storePayoutService->createPayout(
            $request->user(),
            $request->validated()
        );
        $payout->loadMissing('orderStore.order', 'orderStore.store.user');

        $this->notifications->payoutCreated(
            $request->user(),
            $payout
        );

        return $this->successResponse(
            new StorePayoutResource($payout),
            'Payout created successfully.',
            201
        );
    }

    /**
     * Mark a payout as completed or failed.
     */
    public function update(UpdateStorePayoutRequest $request, StorePayout $storePayout)
    {
        $payout = $request->validated('status') === 'completed'
            ? $this->storePayoutService->markCompleted($storePayout, $request->validated('transfer_reference'))
            : $this->storePayoutService->markFailed($storePayout, $request->validated('transfer_reference'));
        $payout->loadMissing('orderStore.order', 'orderStore.store.user');

        $this->notifications->payoutStatusChanged(
            $request->user(),
            $payout
        );

        return $this->successResponse(
            new StorePayoutResource($payout),
            'Payout status updated successfully.'
        );
    }
}
