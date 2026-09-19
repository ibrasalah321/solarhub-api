<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreWalletProviderRequest;
use App\Http\Requests\Wallet\UpdateWalletProviderRequest;
use App\Http\Resources\Wallet\WalletProviderResource;
use App\Models\WalletProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletProviderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return WalletProviderResource::collection(
            WalletProvider::where('status', 'active')->get()
        );
    }

    public function store(StoreWalletProviderRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('wallet_providers', 'supabase_public');
        }

        $provider = WalletProvider::create($data);
        return (new WalletProviderResource($provider))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): WalletProviderResource
    {
        return new WalletProviderResource(WalletProvider::findOrFail($id));
    }

    public function update(UpdateWalletProviderRequest $request, int $id): WalletProviderResource
    {
        $provider = WalletProvider::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('wallet_providers', 'supabase_public');
        }

        $provider->update($data);
        return new WalletProviderResource($provider);
    }

    public function destroy(int $id): JsonResponse
    {
        WalletProvider::findOrFail($id)->delete();
        return response()->json(['message' => 'Wallet provider deactivated successfully']);
    }
}