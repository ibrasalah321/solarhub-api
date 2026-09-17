<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\StoreUserWalletRequest;
use App\Http\Requests\Wallet\UpdateUserWalletRequest;
use App\Http\Resources\Wallet\UserWalletResource;
use App\Models\UserWallet;
use App\Services\Wallet\UserWalletService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class UserWalletController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly UserWalletService $userWalletService
    ) {
    }

    /**
     * Display the authenticated user's wallets.
     */
    public function index(Request $request)
    {
        $wallets = $this->userWalletService->getMyWallets($request->user());

        return $this->successResponse(
            UserWalletResource::collection($wallets),
            'Wallets retrieved successfully.'
        );
    }

    /**
     * Display a single wallet.
     */
    public function show(Request $request, UserWallet $userWallet)
    {
        abort_unless(
            $userWallet->user_id === $request->user()->id,
            403,
            'You are not allowed to view this wallet.'
        );

        return $this->successResponse(
            new UserWalletResource($userWallet->load('walletProvider')),
            'Wallet retrieved successfully.'
        );
    }

    /**
     * Store a new wallet for the authenticated user.
     */
    public function store(StoreUserWalletRequest $request)
    {
        $wallet = $this->userWalletService->createWallet(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new UserWalletResource($wallet),
            'Wallet added successfully.',
            201
        );
    }

    /**
     * Update an existing wallet.
     */
    public function update(UpdateUserWalletRequest $request, UserWallet $userWallet)
    {
        $wallet = $this->userWalletService->updateWallet(
            $request->user(),
            $userWallet,
            $request->validated()
        );

        return $this->successResponse(
            new UserWalletResource($wallet),
            'Wallet updated successfully.'
        );
    }

    /**
     * Delete a wallet.
     */
    public function destroy(Request $request, UserWallet $userWallet)
    {
        $this->userWalletService->deleteWallet(
            $request->user(),
            $userWallet
        );

        return $this->successResponse(
            null,
            'Wallet deleted successfully.'
        );
    }
}
