<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * كاش القوائم العامة (المنتجات، المتاجر، المهندسين، واجهة الهبوط) مع إبطال تلقائي.
 *
 * كل مفتاح يحمل "رقم إصدار"؛ عند تعديل أي منتج/متجر/مهندس يُرفع رقم الإصدار
 * فتصبح كل المفاتيح القديمة غير مستخدمة مباشرة (بدل الانتظار 6–12 ساعة كما كان).
 */
class PublicCache
{
    private const VERSION_KEY = 'public_cache_version';

    public const TTL_MINUTES = 30;

    public static function key(string $name): string
    {
        return $name.':v'.self::version();
    }

    public static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn () => 1);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    public static function remember(string $name, \Closure $callback): mixed
    {
        return Cache::remember(self::key($name), now()->addMinutes(self::TTL_MINUTES), $callback);
    }
}
