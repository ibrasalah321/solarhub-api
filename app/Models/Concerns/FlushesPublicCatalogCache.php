<?php

namespace App\Models\Concerns;

use App\Support\PublicCatalogCache;

/**
 * يحذف كاش القوائم العامة المرتبطة بالموديل عند الإضافة أو التعديل (saved) أو الحذف (deleted).
 *
 * الموديل يعرّف الدالة publicCatalogCacheGroups() ويرجع منها أسماء القوائم المتأثرة.
 * ملاحظة: التحديث الجماعي (Model::where(...)->update()) لا يطلق أحداث الموديل.
 */
trait FlushesPublicCatalogCache
{
    /** @return array<int, string> */
    abstract public static function publicCatalogCacheGroups(): array;

    public static function bootFlushesPublicCatalogCache(): void
    {
        $flush = static fn () => PublicCatalogCache::flush(...static::publicCatalogCacheGroups());

        static::saved($flush);
        static::deleted($flush);
    }
}
