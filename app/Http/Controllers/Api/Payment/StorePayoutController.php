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

    /**
     * المورد يطلب صرف مستحقاته: يُرسل إشعار للإدارة لإنشاء سجلات الصرف.
     * (كانت الواجهة تستدعي هذا المسار وهو غير موجود فيفشل طلب السحب دائماً.)
     */
    public function requestWithdrawal(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $store = $user->store;

        abort_unless($store, 404, 'Store profile not found.');

        // المتاح للطلب = الطلبات الفرعية المكتملة التي لم يُنشأ لها سجل صرف بعد.
        $available = (float) \App\Models\OrderStore::query()
            ->where('store_id', $store->id)
            ->where('status', 'completed')
            ->whereDoesntHave('payout', fn ($q) => $q->where('status', '!=', 'failed'))
            ->sum('subtotal');

        abort_if(
            round((float) $data['amount'], 2) > round($available, 2),
            422,
            'المبلغ المطلوب أكبر من المستحقات المتاحة للصرف ('.number_format($available, 2).').'
        );

        $eventId = (string) \Illuminate\Support\Str::uuid();
        $now = now();
        $admins = \App\Models\User::role('admin')->where('status', 'active')->get(['id']);

        \App\Models\Notification::query()->insert($admins->map(fn ($admin) => [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'payout_request',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $admin->id,
            'sender_id' => $user->id,
            'sender_type' => 'user',
            'template_code' => null,
            'event_id' => $eventId,
            'event_key' => 'payout_request:'.$eventId,
            'dedupe_key' => hash('sha256', implode('|', [$admin->id, 'payout_request', $eventId])),
            'data' => json_encode([
                'title' => 'طلب صرف مستحقات',
                'body' => "المتجر {$store->company_name} يطلب صرف مبلغ ".number_format((float) $data['amount'], 2)
                    .($data['notes'] ?? null ? ' — ملاحظات: '.$data['notes'] : ''),
                'action_url' => '/admin/finance/payouts',
                'store_id' => $store->id,
                'amount' => (float) $data['amount'],
            ], JSON_UNESCAPED_UNICODE),
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());

        return $this->successResponse([
            'requested_amount' => (float) $data['amount'],
            'available_amount' => round($available, 2),
        ], 'تم إرسال طلب صرف المستحقات للإدارة.', 201);
    }
}
