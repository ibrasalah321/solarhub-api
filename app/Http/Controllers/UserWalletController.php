<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserWalletRequest;
use App\Models\UserWallet;

class UserWalletController extends Controller
{
    public function index() { return response()->json(UserWallet::with(['user', 'walletProvider'])->get()); }
    public function store(StoreUserWalletRequest $request) { return response()->json(UserWallet::create($request->validated()), 201); }
    public function show($id) { return response()->json(UserWallet::with(['user', 'walletProvider'])->findOrFail($id)); }
    public function update(StoreUserWalletRequest $request, $id) { $wallet = UserWallet::findOrFail($id); $wallet->update($request->validated()); return response()->json($wallet); }
    public function destroy($id) { UserWallet::findOrFail($id)->delete(); return response()->json(['message' => 'User wallet deleted successfully']); }
}