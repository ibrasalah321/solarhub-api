<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RoleLoginRequest;
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
    ) {}

    /**
     * Log in with email or phone
     * and password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticationService->login(
            $request->validated()
        );

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'Logged in successfully.'
        );
    }

    /**
     * Log in for a specific account role.
     */
    public function loginForRole(
        RoleLoginRequest $request
    ): JsonResponse {
        $result = $this->authenticationService->login(
            $request->validated(),
            $request->route('accountRole')
        );

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'Logged in successfully.'
        );
    }

    /**
     * Log out by revoking the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authenticationService->logout(
            $request->user()
        );

        return $this->successResponse(
            null,
            'Logged out successfully.'
        );
    }

    /**
     * Display the authenticated user's profile.
     *
     * Include the linked store information
     * so the supplier frontend can access
     * auth.user.store.id.
     */
    public function me(Request $request): JsonResponse
    {
        // Get the authenticated user
        // and load the required relationships.
        $user = $request->user()->load([
            'governorate',
            'store',
            'engineerProfile',
        ]);

        // Preserve the existing UserResource
        // fields and response structure.
        $profile = (new UserResource($user))
            ->resolve($request);

        // Include the supplier's linked store.
        // Return null if no store exists.
        $profile['store'] = $user->store
            ? [
                'id' => $user->store->id,
                'user_id' => $user->store->user_id,
                'company_name' => $user->store->company_name,
                'approval_status' => $user->store->approval_status,
                'store_type' => $user->store->store_type,
            ]
            : null;

        return $this->successResponse(
            $profile,
            'Profile retrieved successfully.'
        );
    }
}
