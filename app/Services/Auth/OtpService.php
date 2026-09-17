<?php

namespace App\Services\Auth;

use App\Mail\VerificationOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class OtpService
{
    public function generate(User $user): EmailOtp
    {
        $this->invalidate($user);

        $plainCode = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $expiresInMinutes = max(
            1,
            (int) config(
                'verification.otp.expires_minutes',
                10
            )
        );

        $otp = $user->emailOtps()->create([
            'code' => Hash::make($plainCode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(
                $expiresInMinutes
            ),
            'invalidated_at' => null,
        ]);

        Mail::to($user->email)->send(
            new VerificationOtpMail(
                code: $plainCode,
                expiresInMinutes: $expiresInMinutes
            )
        );

        return $otp;
    }

    public function verify(
        string $email,
        string $plainCode
    ): User {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (
            ! $user ||
            $user->email_verified_at !== null
        ) {
            $this->throwInvalidCode();
        }

        $result = DB::transaction(
            function () use ($user, $plainCode): array {
                $otp = EmailOtp::query()
                    ->where('user_id', $user->id)
                    ->whereNull('invalidated_at')
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if (! $otp) {
                    return [
                        'status' => 'invalid',
                    ];
                }

                if ($otp->isExpired()) {
                    $otp->update([
                        'invalidated_at' => now(),
                    ]);

                    return [
                        'status' => 'expired',
                    ];
                }

                if (! Hash::check($plainCode, $otp->code)) {
                    $attempts = $otp->attempts + 1;

                    $maxAttempts = max(
                        1,
                        (int) config(
                            'verification.otp.max_attempts',
                            5
                        )
                    );

                    $otp->update([
                        'attempts' => $attempts,
                        'invalidated_at' => $attempts >= $maxAttempts
                            ? now()
                            : null,
                    ]);

                    return [
                        'status' => 'invalid',
                    ];
                }

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $otp->update([
                    'invalidated_at' => now(),
                ]);

                return [
                    'status' => 'verified',
                    'user' => $user->fresh(),
                ];
            }
        );

        if ($result['status'] === 'expired') {
            throw ValidationException::withMessages([
                'code' => [
                    'The verification code has expired. Request a new code.',
                ],
            ]);
        }

        if ($result['status'] !== 'verified') {
            $this->throwInvalidCode();
        }

        return $result['user'];
    }

    public function resend(string $email): ?EmailOtp
    {
        $rateLimitKey = 'otp-resend:'.hash(
            'sha256',
            $email
        );

        $maxAttempts = max(
            1,
            (int) config(
                'verification.otp.resend_max_attempts',
                3
            )
        );

        $decaySeconds = max(
            1,
            (int) config(
                'verification.otp.resend_decay_seconds',
                60
            )
        );

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                $maxAttempts
            )
        ) {
            $retryAfter = RateLimiter::availableIn(
                $rateLimitKey
            );

            throw new TooManyRequestsHttpException(
                $retryAfter,
                'Too many OTP resend attempts. Please try again later.'
            );
        }

        RateLimiter::hit(
            $rateLimitKey,
            $decaySeconds
        );

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (
            ! $user ||
            $user->email_verified_at !== null
        ) {
            return null;
        }

        return $this->generate($user);
    }

    public function invalidate(User $user): void
    {
        EmailOtp::query()
            ->where('user_id', $user->id)
            ->whereNull('invalidated_at')
            ->update([
                'invalidated_at' => now(),
            ]);
    }

    private function throwInvalidCode(): never
    {
        throw ValidationException::withMessages([
            'code' => [
                'The verification code is invalid.',
            ],
        ]);
    }
}
