<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserWalletRequest;
use App\Models\UserWallet;
use Illuminate\Http\JsonResponse;

class UserWalletController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(UserWallet::with(['user', 'walletProvider'])->get());
    }

    public function store(StoreUserWalletRequest $request): JsonResponse
    {
        $wallet = UserWallet::create($request->validated());
        return response()->json($wallet, 201);
    }

    public function show($id): JsonResponse
    {
        $wallet = UserWallet::with(['user', 'walletProvider', 'payouts'])->findOrFail($id);
        return response()->json($wallet);
    }

    public function update(StoreUserWalletRequest $request, $id): JsonResponse
    {
        $wallet = UserWallet::findOrFail($id);
        $wallet->update($request->validated());
        return response()->json($wallet);
    }

    public function destroy($id): JsonResponse
    {
        UserWallet::findOrFail($id)->delete();
        return response()->json(['message' => 'Wallet account deleted successfully']);
    }
}