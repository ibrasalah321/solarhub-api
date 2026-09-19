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
        $result = DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);

            $user->assignRole($data['role']);

            $otpData = $this->otpService->generate($user);

            return [
                'user' => $user,
                'plain_code' => $otpData['plain_code'],
            ];
        });

        $this->otpService->sendOtpEmail(
            $result['user'],
            $result['plain_code']
        );

        return $result['user']->load('roles');
    }
}