<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletProviderRequest;
use App\Models\WalletProvider;
use Illuminate\Http\JsonResponse;

class WalletProviderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WalletProvider::all());
    }

    public function store(StoreWalletProviderRequest $request): JsonResponse
    {
        $provider = WalletProvider::create($request->validated());
        return response()->json($provider, 201);
    }

    public function show($id): JsonResponse
    {
        $provider = WalletProvider::with('userWallets')->findOrFail($id);
        return response()->json($provider);
    }

    public function update(StoreWalletProviderRequest $request, $id): JsonResponse
    {
        $provider = WalletProvider::findOrFail($id);
        $provider->update($request->validated());
        return response()->json($provider);
    }

    public function destroy($id): JsonResponse
    {
        WalletProvider::findOrFail($id)->delete();
        return response()->json(['message' => 'Wallet provider removed successfully']);
    }
}