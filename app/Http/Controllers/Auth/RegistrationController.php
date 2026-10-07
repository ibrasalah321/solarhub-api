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
        $user = $this->registrationService->registerForRole(
            $request->validated(),
            $request->route('accountRole')
        );

        $this->notifications->accountCreated(
            $user,
            $request->route('accountRole')
        );

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $user = $this->registrationService->register($data);

        $this->notifications->accountCreated($user, $data['role']);

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }
}
