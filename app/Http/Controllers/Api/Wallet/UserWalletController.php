<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreUserWalletRequest;
use App\Http\Requests\Wallet\UpdateUserWalletRequest;
use App\Http\Resources\Wallet\UserWalletResource;
use App\Models\UserWallet;
use App\Services\Wallet\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserWalletController extends Controller
{
    public function __construct(
        protected UserWalletService $walletService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $wallets = UserWallet::with('provider')
            ->where('user_id', $request->user()->id)
            ->get();

        return UserWalletResource::collection($wallets);
    }

    public function store(StoreUserWalletRequest $request): JsonResponse
    {
        $wallet = $this->walletService->createWallet(
            $request->user()->id,
            $request->validated()
        );

        return (new UserWalletResource($wallet->load('provider')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): UserWalletResource
    {
        $wallet = UserWallet::with('provider')->findOrFail($id);
        return new UserWalletResource($wallet);
    }

    public function update(UpdateUserWalletRequest $request, int $id): UserWalletResource
    {
        $wallet = UserWallet::findOrFail($id);
        $updated = $this->walletService->updateWallet($wallet, $request->validated());
        return new UserWalletResource($updated->load('provider'));
    }

    public function destroy(int $id): JsonResponse
    {
        UserWallet::findOrFail($id)->delete();
        return response()->json(['message' => 'Wallet removed successfully']);
    }
}