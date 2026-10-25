<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RoleRegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegistrationService;
use App\Services\Notification\DomainNotificationDispatcher;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly DomainNotificationDispatcher $notifications
    ) {}

    public function registerForRole(RoleRegisterRequest $request): JsonResponse
    {
        @set_time_limit(180);

        $user = $this->registrationService->registerForRole(
            $request->validated(),
            $request->route('accountRole')
        );

        try {
            $this->notifications->accountCreated(
                $user,
                $request->route('accountRole')
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Registration notification dispatch skipped or failed: ' . $e->getMessage());
        }

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        @set_time_limit(180);

        $data = $request->validated();
        $user = $this->registrationService->register($data);

        try {
            $this->notifications->accountCreated($user, $data['role']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Registration notification dispatch skipped or failed: ' . $e->getMessage());
        }

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }
}
