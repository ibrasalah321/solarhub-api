<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::with(['governorate', 'store', 'engineerProfile'])->paginate(20));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function show($id): JsonResponse
    {
        $user = User::with(['governorate', 'store', 'engineerProfile', 'userWallets'])->findOrFail($id);
        return response()->json($user);
    }

    public function update(StoreUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function destroy($id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User account deleted successfully']);
    }
}