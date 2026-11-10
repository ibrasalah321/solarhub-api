<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * كاش لقوائم البيانات العامة الثابتة (المحافظات، التخصصات، أنواع الخدمات، العلامات، التصنيفات، المحافظ).
 *
 * - مدة الكاش ساعة.
 * - يُحذف تلقائياً عند أي إضافة أو تعديل أو حذف عبر أحداث saved/deleted
 *   (انظر App\Models\Concerns\FlushesPublicCatalogCache).
 * - نخزّن مصفوفة جاهزة (resolve) وليس كائن Response، فيُعاد بناء الرد في كل طلب.
 */
class PublicCatalogCache
{
    public const TTL_SECONDS = 3600;

    public const GOVERNORATES = 'governorates';
    public const SPECIALIZATIONS = 'specializations';
    public const SERVICE_TYPES = 'service_types';
    public const BRANDS = 'brands';
    public const CATEGORIES = 'categories';
    public const WALLET_PROVIDERS = 'wallet_providers';

    /** كل المتغيرات الممكنة لكل قائمة، حتى نحذفها كلها عند التغيير */
    private const VARIANTS = ['default', 'all'];

    public static function remember(string $group, Closure $callback, string $variant = 'default'): mixed
    {
        return Cache::remember(self::key($group, $variant), self::TTL_SECONDS, $callback);
    }

    public static function flush(string ...$groups): void
    {
        foreach ($groups as $group) {
            foreach (self::VARIANTS as $variant) {
                Cache::forget(self::key($group, $variant));
            }
        }
    }

    public static function key(string $group, string $variant = 'default'): string
    {
        return "public_catalog:{$group}:{$variant}";
    }
}
