<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegistrationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly RegistrationService $registrationService
    ) {
    }

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $user = $this->registrationService->register(
            $request->validated()
        );

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }

    public function registerCustomer(
        RegisterRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['role'] = 'customer';
        $user = $this->registrationService->register($data);

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }

    public function registerEngineer(
        RegisterRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['role'] = 'engineer';
        $user = $this->registrationService->register($data);

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }

    public function registerSupplier(
        RegisterRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['role'] = 'supplier';
        $user = $this->registrationService->register($data);

        return $this->successResponse(
            new UserResource($user),
            'Registration successful. Check your email for the verification code.',
            201
        );
    }
}
