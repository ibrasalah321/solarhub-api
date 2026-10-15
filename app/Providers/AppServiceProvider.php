<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        ResetPassword::createUrlUsing(
            function (User $user, string $token): string {
                return rtrim((string) config('app.frontend_url'), '/')
                    . '/reset-password?'
                    . http_build_query([
                        'token' => $token,
                        'email' => $user->getEmailForPasswordReset(),
                    ]);
            }
        );

        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $login = strtolower(
                trim((string) $request->input('login'))
            );

            return Limit::perMinute(5)->by(
                'login:' . hash('sha256', $login . '|' . $request->ip())
            );
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            $email = strtolower(
                trim((string) $request->input('email'))
            );

            return Limit::perMinute(3)->by(
                'forgot-password:' . hash('sha256', $email . '|' . $request->ip())
            );
        });

        RateLimiter::for('otp-verify', function (Request $request): Limit {
            return Limit::perMinute(10)->by(
                $this->otpRateLimitKey($request, 'otp-verify')
            );
        });

        RateLimiter::for('otp-resend', function (Request $request): Limit {
            $maximumAttempts = max(
                1,
                (int) config('verification.otp.resend_max_attempts', 3)
            );

            return Limit::perMinute($maximumAttempts)->by(
                $this->otpRateLimitKey($request, 'otp-resend')
            );
        });
    }

    private function otpRateLimitKey(
        Request $request,
        string $action
    ): string {
        $email = strtolower(
            trim((string) $request->input('email'))
        );

        return $action . ':' . hash(
            'sha256',
            $email . '|' . $request->ip()
        );
    }
}
