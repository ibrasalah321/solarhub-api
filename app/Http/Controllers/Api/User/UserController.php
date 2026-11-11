<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateAdminUserRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display all user accounts (administrative use).
     *
     * NOTE: intended for platform administrators. See the module-level note
     * regarding the current absence of a role/permission system.
     */
    public function index()
    {
        $users = $this->userService->list();

        return $this->successResponse(
            UserResource::collection($users),
            'Users retrieved successfully.'
        );
    }

    /**
     * Create a new administrator account (administrative action).
     */
    public function store(CreateAdminUserRequest $request)
    {
        // إنشاء حساب مدير جديد مسموح للمدير العام فقط،
        // وليس لأي مشرف مقيّد يملك users.manage (وإلا يستطيع رفع صلاحياته بنفسه).
        abort_unless(
            $request->user()?->isSuperAdmin(),
            403,
            'إنشاء حسابات المدراء متاح للمدير العام فقط.'
        );

        $user = $this->userService->createAdmin($request->validated());

        return $this->successResponse(
            new UserResource($user),
            'تم إنشاء حساب المسؤول بنجاح.',
            201
        );
    }

    /**
     * Display a single user account.
     */
    public function show(User $user)
    {
        return $this->successResponse(
            new UserResource($user->load(['governorate', 'store', 'engineerProfile'])),
            'User retrieved successfully.'
        );
    }

    /**
     * Update the authenticated user's own profile.
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new UserResource($user),
            'Profile updated successfully.'
        );
    }

    /**
     * Update a user account's status (administrative action).
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user)
    {
        abort_if($request->user()->id === $user->id, 422, 'لا يمكنك تغيير حالة حسابك بنفسك.');

        abort_if(
            $user->hasRole('admin') && ! $request->user()->isSuperAdmin(),
            403,
            'تغيير حالة حسابات المدراء متاح للمدير العام فقط.'
        );

        $user = $this->userService->updateStatus($user, $request->validated('status'));

        return $this->successResponse(
            new UserResource($user),
            'User status updated successfully.'
        );
    }

    /**
     * Delete the authenticated user's own account.
     */
    public function destroy(Request $request)
    {
        $this->userService->deleteAccount($request->user());

        return $this->successResponse(null, 'Account deleted successfully.');
    }

    /**
     * إنهاء كل الجلسات الأخرى (حذف كل التوكنات ما عدا التوكن الحالي).
     */
    public function revokeOtherSessions(Request $request)
    {
        $user = $request->user();
        $token = $user->currentAccessToken();
        $currentId = $token instanceof \Laravel\Sanctum\PersonalAccessToken ? $token->id : null;

        $revoked = $user->tokens()
            ->when($currentId, fn ($q) => $q->where('id', '!=', $currentId))
            ->delete();

        return $this->successResponse(
            ['revoked_count' => $revoked],
            'تم إنهاء كافة الجلسات الأخرى بنجاح.'
        );
    }
}
