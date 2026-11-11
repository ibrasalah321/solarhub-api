<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يضع حداً أعلى لقيمة per_page في كل طلبات الـ API.
 *
 * كثير من الخدمات تقرأ request()->input('per_page') مباشرة بدون حد،
 * فطلب مثل per_page=1000000 كان يُحمّل الجدول كاملاً ويُسقط الخادم.
 */
class ClampPagination
{
    public const MAX_PER_PAGE = 100;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('per_page')) {
            $perPage = (int) $request->input('per_page');

            if ($perPage < 1) {
                $perPage = 15;
            }

            $request->merge(['per_page' => min($perPage, self::MAX_PER_PAGE)]);
            $request->query->set('per_page', min($perPage, self::MAX_PER_PAGE));
        }

        if ($request->has('page')) {
            $request->merge(['page' => max(1, (int) $request->input('page'))]);
        }

        return $next($request);
    }
}
