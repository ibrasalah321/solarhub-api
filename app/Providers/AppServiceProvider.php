<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }

    /**
     * Configure the application rate limiters.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for(
            'otp-verify',
            function (Request $request): Limit {
                return Limit::perMinute(10)->by(
                    $this->otpRateLimitKey(
                        $request,
                        'otp-verify'
                    )
                );
            }
        );

        RateLimiter::for(
            'otp-resend',
            function (Request $request): Limit {
                $maximumAttempts = max(
                    1,
                    (int) config(
                        'verification.otp.resend_max_attempts',
                        3
                    )
                );

                return Limit::perMinute($maximumAttempts)->by(
                    $this->otpRateLimitKey(
                        $request,
                        'otp-resend'
                    )
                );
            }
        );
    }

    /**
     * Build a secure rate-limit key using email and IP address.
     */
    private function otpRateLimitKey(
        Request $request,
        string $action
    ): string {
        $email = strtolower(
            trim((string) $request->input('email'))
        );

        return $action.':'.hash(
            'sha256',
            $email.'|'.$request->ip()
        );
    }
}
