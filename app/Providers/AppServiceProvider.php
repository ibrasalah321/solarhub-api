<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
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
            function (object $notifiable, string $token): string {
                return rtrim(
                    (string) config('app.frontend_url'),
                    '/'
                )
                .'/reset-password?token='.urlencode($token)
                .'&email='.urlencode($notifiable->getEmailForPasswordReset());
            }
        );

        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        ResetPassword::createUrlUsing(function (User $user, string $token) {
    return rtrim(config('app.frontend_url'), '/')
        . '/reset-password?'
        . http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);
});
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                'login:'.hash('sha256', strtolower(trim((string) $request->input('login'))).'|'.$request->ip())
            );
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            return Limit::perMinute(3)->by(
                'forgot-password:'.hash('sha256', strtolower(trim((string) $request->input('email'))).'|'.$request->ip())
            );
        });

        RateLimiter::for('otp-verify', function (Request $request): Limit {
            return Limit::perMinute(10)->by($this->otpRateLimitKey($request, 'otp-verify'));
        });

        RateLimiter::for('otp-resend', function (Request $request): Limit {
            $maximumAttempts = max(1, (int) config('verification.otp.resend_max_attempts', 3));

            return Limit::perMinute($maximumAttempts)->by(
                $this->otpRateLimitKey($request, 'otp-resend')
            );
        });
    }
);
}

    private function otpRateLimitKey(Request $request, string $action): string
    {
        $email = strtolower(trim((string) $request->input('email')));

        return $action.':'.hash('sha256', $email.'|'.$request->ip());
    }
}
