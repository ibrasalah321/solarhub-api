<?php

namespace App\Services\Auth\Store;

use App\Models\Store;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreOnboardingService
{
    public function __construct(
        private readonly SupabaseStorageService $storageService
    ) {
    }

    public function store(User $user, array $data): Store
    {
        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => [
                    'Please verify your email before completing Store onboarding.',
                ],
            ]);
        }

        if ($user->store()->exists()) {
            throw ValidationException::withMessages([
                'store' => [
                    'Store onboarding has already been submitted.',
                ],
            ]);
        }

        $commercialFilePath = null;

        try {

            if (isset($data['commercial_file'])) {
                $commercialFilePath = $this->storageService->uploadPrivate(
                    $data['commercial_file'],
                    'stores/commercial-documents'
                );
            }

            return DB::transaction(function () use (
                $user,
                $data,
                $commercialFilePath
            ): Store {
                return Store::query()->create([
                    'user_id' => $user->id,

                    'company_name' => $data['company_name'],

                    'commercial_registry' =>
                        $data['commercial_registry'] ?? null,

                    'commercial_file_path' =>
                        $commercialFilePath,
                    'store_type' =>
                        $data['store_type'],
                    
                    'address_details' => $data['address_details'],

                    'approval_status' => 'pending',

                    'rejection_reason' => null,

                    'approved_at' => null,
                ]);
            });
        } catch (Throwable $exception) {

            if ($commercialFilePath) {
                $this->storageService->deletePrivate(
                    $commercialFilePath
                );
            }

            throw $exception;
        }
    }

    public function status(User $user): ?Store
    {
        return $user->store;
    }
}