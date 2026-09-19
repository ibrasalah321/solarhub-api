<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreWalletProviderRequest;
use App\Http\Requests\Wallet\UpdateWalletProviderRequest;
use App\Http\Resources\Wallet\WalletProviderResource;
use App\Models\WalletProvider;
use App\Services\SupabaseStorageService;
use App\Traits\ApiResponseTrait;

class WalletProviderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    /**
     * Display all active wallet providers.
     */
    public function index()
    {
        $walletProviders = WalletProvider::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return $this->successResponse(
            WalletProviderResource::collection($walletProviders),
            'Wallet providers retrieved successfully.'
        );
    }

    /**
     * Display a single wallet provider.
     */
    public function show(WalletProvider $walletProvider)
    {
        return $this->successResponse(
            new WalletProviderResource($walletProvider),
            'Wallet provider retrieved successfully.'
        );
    }

    /**
     * Store a new wallet provider.
     */
    public function store(StoreWalletProviderRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $this->storageService->uploadPublic(
                $request->file('logo'),
                'wallet-providers'
            );
        }

        unset($data['logo']);

        $walletProvider = WalletProvider::create($data);

        return $this->successResponse(
            new WalletProviderResource($walletProvider),
            'Wallet provider created successfully.',
            201
        );
    }

    /**
     * Update an existing wallet provider.
     */
    public function update(UpdateWalletProviderRequest $request, WalletProvider $walletProvider)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $this->storageService->deletePublic($walletProvider->logo_path);

            $data['logo_path'] = $this->storageService->uploadPublic(
                $request->file('logo'),
                'wallet-providers'
            );
        }

        unset($data['logo']);

        $walletProvider->update($data);

        return $this->successResponse(
            new WalletProviderResource($walletProvider),
            'Wallet provider updated successfully.'
        );
    }

    /**
     * Delete a wallet provider.
     */
    public function destroy(WalletProvider $walletProvider)
    {
        abort_if(
            $walletProvider->userWallets()->exists() || $walletProvider->orderPayments()->exists(),
            409,
            'This wallet provider is already in use and cannot be deleted.'
        );

        $this->storageService->deletePublic($walletProvider->logo_path);

        $walletProvider->delete();

        return $this->successResponse(
            null,
            'Wallet provider deleted successfully.'
        );
    }
}
