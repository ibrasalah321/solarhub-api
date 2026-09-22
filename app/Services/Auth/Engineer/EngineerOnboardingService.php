<?php

namespace App\Services\Auth\Engineer;

use App\Models\EngineerProfile;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class EngineerOnboardingService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function store(User $user, array $data): EngineerProfile
    {
        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => [
                    'Please verify your email before completing Engineer onboarding.',
                ],
            ]);
        }

        if ($user->engineerProfile()->exists()) {
            
            throw ValidationException::withMessages([
                'engineer' => [
                    'Enginner onboarding has already been submitted.',
                ],
            ]);
        }

        $cvPath = null;
        

        try {
                $cvPath = $this->storageService->uploadPrivate($data['cv'],'engineers/cvs');

            return DB::transaction(function () use (
                $user,
                $data,
                $cvPath,
            ): EngineerProfile {
                return EngineerProfile::query()->create([
                    'user_id' => $user->id,

                    'license_number' => $data['license_number'] ?? null,

                    'cv_path' => $cvPath,

                    'approval_status' => 'pending',

                    'rejection_reason' => null,

                    'approved_at' => null,
                ]);
            });
        } catch (Throwable $exception) {

            if ($cvPath) {
                $this->storageService->deletePrivate($cvPath);
            }

            throw $exception;
        }
    }
    public function status(User $user): ?EngineerProfile
    {
        return $user->engineerProfile;
    }

}