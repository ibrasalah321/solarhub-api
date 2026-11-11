<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // حد عام لعدد الطلبات على الـ API (المعرّف في AppServiceProvider باسم api)
        $middleware->throttleApi();

        // حد أعلى لـ per_page في كل الطلبات
        $middleware->api(append: [
            \App\Http\Middleware\ClampPagination::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'approved' => \App\Http\Middleware\EnsureProfileIsApproved::class,
        ]);

        // لا يوجد مسار login في الويب: طلبات API غير المصادق عليها ترجع 401 JSON بدل خطأ 500
        $middleware->redirectGuestsTo(
            fn (\Illuminate\Http\Request $request) => $request->is('api/*') ? null : '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (\Illuminate\Http\Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        // أخطاء قاعدة البيانات في الـ API ترجع رسالة عامة دائماً (حتى مع APP_DEBUG=true):
        // نص QueryException يحتوي جملة SQL وعنوان الخادم وقيم الحقول (مثل hash كلمة المرور).
        // التفاصيل تبقى في storage/logs عبر report() الافتراضي.
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'حدث خطأ في الخادم، يرجى المحاولة لاحقاً.',
                ], 500);
            }

            return null;
        });
    })->create();
