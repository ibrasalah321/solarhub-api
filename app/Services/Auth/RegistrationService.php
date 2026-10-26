<?php

namespace App\Services\Auth;

use App\Models\EngineerProfile;
use App\Models\Governorate;
use App\Models\Store;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Throwable;

class RegistrationService
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly SupabaseStorageService $storageService
    ) {}

    public function register(array $data): User
    {
        $result = $this->createAccount(
            $data,
            $data['role'],
            createApplication: false
        );

        return $this->finishRegistration($result);
    }

    public function registerForRole(array $data, string $role): User
    {
        if (! in_array($role, ['customer', 'engineer', 'supplier'], true)) {
            throw new InvalidArgumentException('Unsupported registration role.');
        }

        $uploadedPath = null;

        if ($role === 'engineer' && isset($data['cv'])) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $data['cv'],
                'engineers/cvs'
            );
        }

        if ($role === 'supplier' && isset($data['commercial_file'])) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $data['commercial_file'],
                'stores/commercial-documents'
            );
        }

        try {
            $result = $this->createAccount(
                $data,
                $role,
                $uploadedPath,
                createApplication: true
            );
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $this->storageService->deletePrivate($uploadedPath);
            }

            throw $exception;
        }

        return $this->finishRegistration($result);
    }

    /**
     * @return array{user: User, plain_code: string}
     */
    private function createAccount(
        array $data,
        string $role,
        ?string $uploadedPath = null,
        bool $createApplication = false
    ): array {
        return DB::transaction(function () use (
            $data,
            $role,
            $uploadedPath,
            $createApplication
        ): array {
            $coords = $data['default_coordinates'] ?? $data['location_coordinates'] ?? null;

            $governorateId = $data['governorate_id'] ?? null;
            if (! $governorateId && ! empty($data['governorate'])) {
                $govName = trim($data['governorate']);
                $governorateId = Governorate::query()
                    ->where('name_ar', 'like', "%{$govName}%")
                    ->orWhere('name_en', 'like', "%{$govName}%")
                    ->value('id');
            }

            $user = User::query()->create([
                'governorate_id' => $governorateId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'default_coordinates' => $coords,
            ]);

            $user->assignRole($role);

            if ($createApplication && $role === 'engineer') {
                EngineerProfile::query()->create([
                    'user_id' => $user->id,
                    'license_number' => $data['license_number'],
                    'cv_path' => $uploadedPath,
                    'approval_status' => 'pending',
                    'rejection_reason' => null,
                    'approved_at' => null,
                ]);
            }

            if ($createApplication && $role === 'supplier') {
                Store::query()->create([
                    'user_id' => $user->id,
                    'company_name' => $data['company_name'],
                    'commercial_registry' => $data['commercial_registry'],
                    'commercial_file_path' => $uploadedPath,
                    'store_type' => $data['store_type'],
                    'address_details' => $data['address_details'],
                    'approval_status' => 'pending',
                    'rejection_reason' => null,
                    'approved_at' => null,
                    'location_coordinates' => $coords,
                ]);
            }

            $otpData = $this->otpService->generate($user);

            return [
                'user' => $user,
                'plain_code' => $otpData['plain_code'],
            ];
        });
    }

    /**
     * @param  array{user: User, plain_code: string}  $result
     */
    private function finishRegistration(array $result): User
    {
        $this->otpService->sendOtpEmail(
            $result['user'],
            $result['plain_code']
        );

        return $result['user']->load([
            'roles',
            'engineerProfile',
            'store',
        ]);
    }
}
