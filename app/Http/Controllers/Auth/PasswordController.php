<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PasswordResetService $passwordResetService
    ) {
    }

    /**
     * Request a password reset link.
     *
     * Always returns the same generic success message, whether or not an
     * account exists for the given email, to avoid account enumeration.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->sendResetLink($request->validated('email'));

        return $this->successResponse(
            null,
            'If an account exists for this email address, a password reset link has been sent.'
        );
    }

    /**
     * Reset the password using the token from the reset link email.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->reset($request->validated());

        return $this->successResponse(
            null,
            'Your password has been reset successfully. Please log in with your new password.'
        );
    }
}
