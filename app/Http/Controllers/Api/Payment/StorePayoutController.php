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
     * Display payouts: all for platform admins, or owned payouts for store owners.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user && $user->can('store-payouts.manage')) {
            $payouts = $this->storePayoutService->list($request->query('status'));
        } elseif ($user) {
            $payouts = $this->storePayoutService->getMyStorePayouts($user, $request->query('status'));
        } else {
            abort(401, 'Unauthenticated.');
        }

        return $this->successResponse(
            StorePayoutResource::collection($payouts),
            'Payouts retrieved successfully.'
        );
    }

    /**
     * Display payouts belonging to the authenticated store owner.
     */
    public function myPayouts(Request $request)
    {
        $payouts = $this->storePayoutService->getMyStorePayouts(
            $request->user(),
            $request->query('status')
        );

        return $this->successResponse(
            StorePayoutResource::collection($payouts),
            'Store payouts retrieved successfully.'
        );
    }

    /**
     * Financial summary for the authenticated store owner.
     */
    public function summary(Request $request)
    {
        $summary = $this->storePayoutService->getFinancialSummary($request->user());

        return $this->successResponse(
            $summary,
            'Financial summary retrieved successfully.'
        );
    }

    /**
     * Display a single payout.
     */
    public function show(StorePayout $storePayout)
    {
        return $this->successResponse(
            new StorePayoutResource(
                $storePayout->load(['orderStore.order', 'orderStore.store', 'userWallet.walletProvider'])
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
