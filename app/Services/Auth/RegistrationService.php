<?php

namespace App\Services\Auth;

use App\Models\EngineerProfile;
use App\Models\Governorate;
use App\Models\Store;
use App\Models\User;
use App\Services\SupabaseStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
        $role = $data['role'] ?? 'customer';
        $uploadedPath = null;

        $cvFile = $data['cv'] ?? $data['cvFile'] ?? null;
        if ($role === 'engineer' && $cvFile instanceof UploadedFile) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $cvFile,
                'engineers/cvs'
            );
        }

        $commercialFile = $data['commercial_file'] ?? $data['commercialRegisterFile'] ?? null;
        if ($role === 'supplier' && $commercialFile instanceof UploadedFile) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $commercialFile,
                'stores/commercial-documents'
            );
        }

        $createApplication = ($role === 'engineer' && ! empty($data['license_number']))
            || ($role === 'supplier' && ! empty($data['company_name']));

        try {
            $result = $this->createAccount(
                $data,
                $role,
                $uploadedPath,
                createApplication: $createApplication
            );
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $this->storageService->deletePrivate($uploadedPath);
            }

            throw $exception;
        }

        return $this->finishRegistration($result);
    }

    public function registerForRole(array $data, string $role): User
    {
        if (! in_array($role, ['customer', 'engineer', 'supplier'], true)) {
            throw new InvalidArgumentException('Unsupported registration role.');
        }

        $uploadedPath = null;

        $cvFile = $data['cv'] ?? $data['cvFile'] ?? null;
        if ($role === 'engineer' && $cvFile instanceof UploadedFile) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $cvFile,
                'engineers/cvs'
            );
        }

        $commercialFile = $data['commercial_file'] ?? $data['commercialRegisterFile'] ?? null;
        if ($role === 'supplier' && $commercialFile instanceof UploadedFile) {
            $uploadedPath = $this->storageService->uploadPrivate(
                $commercialFile,
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

            // 3. رقم الجوال محجوز لحساب آخر؟
            //    - حساب غير مفعّل ولا يملك أي بيانات: نحذفه (تسجيل مهجور).
            //    - حساب مفعّل محذوف حذفاً ناعماً أو له بيانات: لا نحذف شيئاً نهائياً،
            //      بل نرفض التسجيل (سابقاً كان يُحذف نهائياً مع متجره ومحافظه).
            $conflictingUsers = User::withTrashed()
                ->where('phone', $phone)
                ->when($existingUser, fn ($q) => $q->where('id', '!=', $existingUser->id))
                ->get();

            foreach ($conflictingUsers as $cUser) {
                $isAbandonedSignup = $cUser->email_verified_at === null
                    && ! $cUser->store()->exists()
                    && ! $cUser->orders()->exists()
                    && ! $cUser->serviceRequests()->exists()
                    && ! $cUser->userWallets()->exists();

                if (! $isAbandonedSignup) {
                    throw ValidationException::withMessages([
                        'phone' => ['رقم الجوال مرتبط بحساب سابق، يرجى التواصل مع الدعم أو استخدام رقم آخر.'],
                    ]);
                }

                $cUser->emailOtps()->delete();
                $cUser->engineerProfile()?->delete();
                $cUser->forceDelete();
            }

            // البريد مستخدم في حساب مفعّل محذوف حذفاً ناعماً → خطأ واضح بدل خطأ 500 من قيد unique
            $trashedVerifiedEmail = User::onlyTrashed()
                ->where('email', $email)
                ->whereNotNull('email_verified_at')
                ->exists();

            if ($trashedVerifiedEmail) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني مرتبط بحساب محذوف، يرجى التواصل مع الدعم لاستعادته.'],
                ]);
            }

            // 4. Resolve governorate_id if available
            $govId = $data['governorate_id'] ?? null;
            if (! $govId && ! empty($data['governorate'])) {
                $govName = str_replace(['%', '_'], ['\\%', '\\_'], trim($data['governorate']));
                $gov = Governorate::query()
                    ->where('name_ar', 'like', "%{$govName}%")
                    ->orWhere('name_en', 'like', "%{$govName}%")
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

                $existingUser->syncRoles([$role]);
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

                $user->assignRole($role);
            }

            if ($createApplication && $role === 'engineer') {
                EngineerProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'license_number' => $data['license_number'] ?? null,
                        'cv_path' => $uploadedPath,
                        'approval_status' => 'pending',
                        'rejection_reason' => null,
                        'approved_at' => null,
                    ]
                );
            }

            if ($createApplication && $role === 'supplier') {
                Store::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $data['company_name'] ?? null,
                        'commercial_registry' => $data['commercial_registry'] ?? null,
                        'commercial_file_path' => $uploadedPath,
                        'store_type' => $data['store_type'] ?? null,
                        'address_details' => $data['address_details'] ?? null,
                        'approval_status' => 'pending',
                        'rejection_reason' => null,
                        'approved_at' => null,
                        'location_coordinates' => $coords,
                    ]
                );
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
