<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\OtpService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    public function verify(
        VerifyOtpRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $user = $this->otpService->verify(
            email: $data['email'],
            plainCode: $data['code']
        );

        return $this->successResponse(
            new UserResource($user),
            'Email verified successfully.'
        );
    }

    public function resend(
        ResendOtpRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $this->otpService->resend(
            email: $data['email']
        );

        return $this->successResponse(
            null,
            'If the account exists and is not verified, a new code has been sent.'
        );
    }
}
