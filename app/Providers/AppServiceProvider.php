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
        \Illuminate\Database\Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new \App\Database\PostgresConnection($connection, $database, $prefix, $config);
        });
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);

        // إبطال كاش القوائم العامة عند أي تعديل على البيانات المعروضة فيها.
        foreach ([
            \App\Models\StoreProduct::class,
            \App\Models\Store::class,
            \App\Models\EngineerProfile::class,
            \App\Models\MasterProduct::class,
            \App\Models\ProductImage::class,
            \App\Models\StoreRating::class,
            \App\Models\EngineerRating::class,
        ] as $model) {
            $model::saved(fn () => \App\Support\PublicCache::flush());
            $model::updated(fn () => \App\Support\PublicCache::flush()); // increment/decrement
            $model::deleted(fn () => \App\Support\PublicCache::flush());
        }

        ResetPassword::createUrlUsing(
            function (User $user, string $token): string {
                // صفحة الواجهة هي /auth/reset-password (تقرأ token و email من الرابط)
                return rtrim((string) config('app.frontend_url'), '/')
                    . '/auth/reset-password?'
                    . http_build_query([
                        'token' => $token,
                        'email' => $user->getEmailForPasswordReset(),
                    ]);
            }
        );

        // المدير العام يتجاوز كل السياسات. المشرف المقيّد بصلاحيات مباشرة لا يتجاوزها.
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }

    private function configureRateLimiting(): void
{
    // الحد العام لكل مسارات الـ API: 120 طلب في الدقيقة لكل مستخدم (أو لكل IP للزوار).
    RateLimiter::for('api', function (Request $request): Limit {
        return Limit::perMinute(120)->by(
            $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip()
        );
    });

    // تحويل الإحداثيات لعنوان يستدعي خدمات خارجية: حد أشد للزوار.
    RateLimiter::for('geocode', function (Request $request): Limit {
        return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('login', function (Request $request): Limit {
        $login = strtolower(trim(
            (string) ($request->input('login') ?? $request->input('email') ?? '')
        ));

        return Limit::perMinute(5)->by(
            $login . '|' . $request->ip()
        );
    });

    RateLimiter::for('forgot-password', function (Request $request): Limit {
        $email = strtolower(trim(
            (string) $request->input('email', '')
        ));

        return Limit::perMinute(5)->by(
            $email . '|' . $request->ip()
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
