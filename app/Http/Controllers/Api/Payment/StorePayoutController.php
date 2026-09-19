<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StoreStorePayoutRequest;
use App\Http\Requests\Payment\UpdateStorePayoutRequest;
use App\Http\Resources\Payment\StorePayoutResource;
use App\Models\Store;
use App\Models\StorePayout;
use App\Services\Payment\StorePayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StorePayoutController extends Controller
{
    public function __construct(
        protected StorePayoutService $payoutService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $payouts = StorePayout::with('store')->latest()->paginate(20);
        return StorePayoutResource::collection($payouts);
    }

    public function store(StoreStorePayoutRequest $request): JsonResponse
    {
        $store = Store::findOrFail($request->validated('store_id'));
        $payout = $this->payoutService->generatePayout(
            $store,
            (float) ($request->validated('commission_percentage') ?? 5.0)
        );

        return (new StorePayoutResource($payout))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): StorePayoutResource
    {
        $payout = StorePayout::with('store')->findOrFail($id);
        return new StorePayoutResource($payout);
    }

    public function update(UpdateStorePayoutRequest $request, int $id): StorePayoutResource
    {
        $payout = StorePayout::findOrFail($id);
        $updated = $this->payoutService->markAsTransferred(
            $payout,
            $request->validated('transfer_reference')
        );

        return new StorePayoutResource($updated);
    }
}