<?php

namespace App\Services\Auth;

use App\Models\Governorate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    public function register(array $data): User
    {
        $result = DB::transaction(function () use ($data): array {
            $email = mb_strtolower(trim($data['email']));
            $phone = trim($data['phone']);

            // 1. Double check verified unique constraints
            $verifiedEmailUser = User::query()
                ->where('email', $email)
                ->whereNotNull('email_verified_at')
                ->whereNull('deleted_at')
                ->first();

            if ($verifiedEmailUser) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني مسجل ومفعل بالفعل، يرجى تسجيل الدخول مباشرة.'],
                ]);
            }

            $verifiedPhoneUser = User::query()
                ->where('phone', $phone)
                ->whereNotNull('email_verified_at')
                ->whereNull('deleted_at')
                ->first();

            if ($verifiedPhoneUser) {
                throw ValidationException::withMessages([
                    'phone' => ['رقم الجوال مسجل ومفعل بالفعل بحساب آخر.'],
                ]);
            }

            // 2. Find existing unverified user by email (even if trashed)
            $existingUser = User::withTrashed()
                ->where('email', $email)
                ->whereNull('email_verified_at')
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                $existingUser->restore();
            }

            // 3. Clean up any conflicting unverified/trashed user holding this phone number
            $conflictingUsers = User::withTrashed()
                ->where('phone', $phone)
                ->where(function ($q) {
                    $q->whereNull('email_verified_at')
                      ->orWhereNotNull('deleted_at');
                })
                ->when($existingUser, fn ($q) => $q->where('id', '!=', $existingUser->id))
                ->get();

            foreach ($conflictingUsers as $cUser) {
                $cUser->emailOtps()->delete();
                $cUser->forceDelete();
            }

            // 4. Resolve governorate_id if available
            $govId = $data['governorate_id'] ?? null;
            if (!$govId && !empty($data['governorate'])) {
                $gov = Governorate::query()
                    ->where('name_ar', 'like', '%' . $data['governorate'] . '%')
                    ->orWhere('name_en', 'like', '%' . $data['governorate'] . '%')
                    ->first();
                if ($gov) {
                    $govId = $gov->id;
                }
            }

            $coords = $data['default_coordinates'] ?? $data['location_coordinates'] ?? null;

            if ($existingUser) {
                $existingUser->update([
                    'name' => $data['name'],
                    'phone' => $phone,
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                    'governorate_id' => $govId ?? $existingUser->governorate_id,
                    'default_coordinates' => $coords ?? $existingUser->default_coordinates,
                ]);

                $existingUser->syncRoles([$data['role']]);
                $user = $existingUser;
            } else {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make($data['password']),
                    'status' => 'active',
                    'governorate_id' => $govId,
                    'default_coordinates' => $coords,
                ]);

                $user->assignRole($data['role']);
            }

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