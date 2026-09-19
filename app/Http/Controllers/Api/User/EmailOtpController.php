<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailOtpRequest;
use App\Models\EmailOtp;
use Illuminate\Http\JsonResponse;

class EmailOtpController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(EmailOtp::with('user')->latest()->paginate(20));
    }

    public function store(StoreEmailOtpRequest $request): JsonResponse
    {
        $otp = EmailOtp::create($request->validated());
        return response()->json($otp, 201);
    }

    public function show($id): JsonResponse
    {
        $otp = EmailOtp::with('user')->findOrFail($id);
        return response()->json($otp);
    }

    public function update(StoreEmailOtpRequest $request, $id): JsonResponse
    {
        $otp = EmailOtp::findOrFail($id);
        $otp->update($request->validated());
        return response()->json($otp);
    }

    public function destroy($id): JsonResponse
    {
        EmailOtp::findOrFail($id)->delete();
        return response()->json(['message' => 'OTP record deleted successfully']);
    }
}