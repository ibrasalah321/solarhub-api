<?php

namespace App\Services\User;

use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    private const EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Generate and send a new OTP to the user's email, invalidating any
     * previous unverified OTPs for that user.
     */
    public function generate(User $user): EmailOtp
    {
        $latest = $user->otps()->latest()->first();

        abort_if(
            $latest
                && $latest->verified_at === null
                && $latest->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS,
            429,
            'Please wait before requesting another verification code.'
        );

        $plainOtp = (string) random_int(100000, 999999);

        $otp = $user->otps()->create([
            'email' => $user->email,
            'otp' => Hash::make($plainOtp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        $this->sendMail($user->email, $plainOtp);

        return $otp;
    }

    /**
     * Verify a submitted OTP for the given email.
     */
    public function verify(string $email, string $submittedOtp): User
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $otp = $user->otps()
            ->whereNull('verified_at')
            ->latest()
            ->first();

        abort_if($otp === null, 422, 'No pending verification code for this account.');

        abort_if(
            $otp->expires_at->isPast(),
            422,
            'This verification code has expired. Please request a new one.'
        );

        abort_if(
            $otp->attempts >= self::MAX_ATTEMPTS,
            429,
            'Too many failed attempts. Please request a new verification code.'
        );

        if (! Hash::check($submittedOtp, $otp->otp)) {
            $otp->increment('attempts');

            abort(422, 'The verification code is incorrect.');
        }

        $otp->update(['verified_at' => now()]);
        $user->update(['email_verified_at' => now()]);

        return $user->fresh();
    }

    private function sendMail(string $email, string $plainOtp): void
    {
        // NOTE: uses whatever MAIL_* configuration is set in .env.
        // No dedicated Mailable class exists yet in this codebase; a plain
        // text message is sent here to keep this service self-contained.
        Mail::raw(
            "Your SolarHub verification code is: {$plainOtp}. It expires in " . self::EXPIRY_MINUTES . ' minutes.',
            function ($message) use ($email) {
                $message->to($email)->subject('Verify your email address');
            }
        );
    }
}
