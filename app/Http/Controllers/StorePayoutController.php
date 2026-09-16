<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorePayoutRequest;
use App\Models\StorePayout;
use Illuminate\Http\JsonResponse;

class StorePayoutController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StorePayout::with(['orderStore.store', 'userWallet.walletProvider'])->paginate(15));
    }

    public function store(StoreStorePayoutRequest $request): JsonResponse
    {
        $payout = StorePayout::create($request->validated());
        return response()->json($payout, 201);
    }

    public function show($id): JsonResponse
    {
        $payout = StorePayout::with(['orderStore.store', 'userWallet.walletProvider'])->findOrFail($id);
        return response()->json($payout);
    }

    public function update(StoreStorePayoutRequest $request, $id): JsonResponse
    {
        $payout = StorePayout::findOrFail($id);
        $payout->update($request->validated());
        return response()->json($payout);
    }

    public function destroy($id): JsonResponse
    {
        $payout = StorePayout::findOrFail($id);
        $payout->delete();
        return response()->json(['message' => 'Payout record deleted successfully']);
    }
}