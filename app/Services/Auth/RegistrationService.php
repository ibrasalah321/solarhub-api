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

            // كل الحسابات (بما فيها المحذوفة ناعماً) التي تستخدم نفس البريد أو نفس الجوال: استعلامان فقط
            // بدل خمسة، والقرارات تُتخذ في الذاكرة (كل استعلام ~0.3 ثانية مع القاعدة البعيدة).
            $emailUsers = User::withTrashed()->where('email', $email)->get();
            $phoneUsers = User::withTrashed()->where('phone', $phone)->get();

            // 1. Double check verified unique constraints
            if ($emailUsers->contains(fn (User $u) => $u->email_verified_at !== null && ! $u->trashed())) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني مسجل ومفعل بالفعل، يرجى تسجيل الدخول مباشرة.'],
                ]);
            }

            if ($phoneUsers->contains(fn (User $u) => $u->email_verified_at !== null && ! $u->trashed())) {
                throw ValidationException::withMessages([
                    'phone' => ['رقم الجوال مسجل ومفعل بالفعل بحساب آخر.'],
                ]);
            }

            // البريد مستخدم في حساب مفعّل محذوف حذفاً ناعماً → خطأ واضح بدل خطأ 500 من قيد unique
            if ($emailUsers->contains(fn (User $u) => $u->email_verified_at !== null && $u->trashed())) {
                throw ValidationException::withMessages([
                    'email' => ['البريد الإلكتروني مرتبط بحساب محذوف، يرجى التواصل مع الدعم لاستعادته.'],
                ]);
            }

            // 2. Find existing unverified user by email (even if trashed)
            $existingUser = $emailUsers->first(fn (User $u) => $u->email_verified_at === null);

            if ($existingUser && $existingUser->trashed()) {
                $existingUser->restore();
            }

            // 3. رقم الجوال محجوز لحساب آخر؟
            //    - حساب غير مفعّل ولا يملك أي بيانات: نحذفه (تسجيل مهجور).
            //    - حساب مفعّل محذوف حذفاً ناعماً أو له بيانات: لا نحذف شيئاً نهائياً،
            //      بل نرفض التسجيل (سابقاً كان يُحذف نهائياً مع متجره ومحافظه).
            $conflictingUsers = $phoneUsers->reject(fn (User $u) => $existingUser && $u->id === $existingUser->id);

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

            // حساب جديد كلياً: لا ملف مهندس ولا متجر سابق، فنُنشئ مباشرة بدل updateOrCreate (select + insert)
            // ونربط العلاقات في الذاكرة لاستجابة التسجيل بدل إعادة تحميلها.
            $isNewUser = $existingUser === null;
            $profile = null;
            $store = null;

            if ($createApplication && $role === 'engineer') {
                $profileData = [
                    'license_number' => $data['license_number'] ?? null,
                    'cv_path' => $uploadedPath,
                    'approval_status' => 'pending',
                    'rejection_reason' => null,
                    'approved_at' => null,
                ];
                $profile = $isNewUser
                    ? EngineerProfile::query()->create(['user_id' => $user->id] + $profileData)
                    : EngineerProfile::query()->updateOrCreate(['user_id' => $user->id], $profileData);
            }

            if ($createApplication && $role === 'supplier') {
                $storeData = [
                    'company_name' => $data['company_name'] ?? null,
                    'commercial_registry' => $data['commercial_registry'] ?? null,
                    'commercial_file_path' => $uploadedPath,
                    'store_type' => $data['store_type'] ?? null,
                    'address_details' => $data['address_details'] ?? null,
                    'approval_status' => 'pending',
                    'rejection_reason' => null,
                    'approved_at' => null,
                    'location_coordinates' => $coords,
                ];
                $store = $isNewUser
                    ? Store::query()->create(['user_id' => $user->id] + $storeData)
                    : Store::query()->updateOrCreate(['user_id' => $user->id], $storeData);
            }

            if ($isNewUser) {
                $user->setRelation('engineerProfile', $profile);
                $user->setRelation('store', $store);
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

        // العلاقات المربوطة في الذاكرة (حساب جديد) لا يُعاد تحميلها
        return $result['user']->loadMissing([
            'roles',
            'engineerProfile',
            'store',
        ]);
    }
}
