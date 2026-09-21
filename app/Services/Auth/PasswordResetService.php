<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Send a password reset link to the given email if an account exists
     * for it. The outcome is intentionally not surfaced to the caller —
     * see PasswordController::forgotPassword() for the unified response.
     */
    public function sendResetLink(string $email): void
    {
        Password::broker()->sendResetLink(['email' => $email]);
    }

    /**
     * Reset a user's password using a Password Broker token, then revoke
     * all of that user's existing Sanctum tokens.
     *
     * @param  array{email: string, token: string, password: string, password_confirmation: string}  $credentials
     */
    public function reset(array $credentials): void
    {
        $status = Password::broker()->reset(
            $credentials,
            function (User $user, string $password): void {
                DB::transaction(function () use ($user, $password): void {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();

                    $user->tokens()->delete();
                });
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [$this->messageFor($status)],
            ]);
        }
    }

    private function messageFor(string $status): string
    {
        return match ($status) {
            PasswordBroker::INVALID_USER => 'We could not find an account with that email address.',
            PasswordBroker::INVALID_TOKEN => 'This password reset token is invalid or has expired.',
            PasswordBroker::RESET_THROTTLED => 'Please wait before retrying this password reset.',
            default => 'Unable to reset the password. Please request a new reset link.',
        };
    }
}
