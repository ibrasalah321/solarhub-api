<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationService
{
    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make(
                    $data['password']
                ),
                'status' => 'active',
            ]);

            $user->assignRole($data['role']);

            $this->otpService->generate($user);

            return $user->load('roles');
        });
    }
}
