<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthenticationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly AuthenticationService $authenticationService
    ) {
    }

    /**
     * Log in with email or phone (via the unified `login` field) and password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticationService->login($request->validated());

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'Logged in successfully.'
        );
    }

    /**
     * Log out by revoking the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authenticationService->logout($request->user());

        return $this->successResponse(null, 'Logged out successfully.');
    }

    /**
     * Display the authenticated user's own profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['governorate', 'store', 'engineerProfile']);

        return $this->successResponse(
            new UserResource($user),
            'Profile retrieved successfully.'
        );
    }
}
