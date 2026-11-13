<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Sanctum يحدّث last_used_at مع كل طلب، أي UPDATE إضافي على قاعدة بيانات بعيدة.
 * هنا نكتفي بتحديثه مرة كل 10 دقائق على الأكثر لكل توكن.
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected const LAST_USED_THROTTLE_SECONDS = 600;

    public function save(array $options = [])
    {
        $dirty = array_keys($this->getDirty());
        $onlyLastUsed = $this->exists
            && $dirty !== []
            && array_diff($dirty, ['last_used_at', 'updated_at']) === [];

        if ($onlyLastUsed) {
            $previous = $this->getOriginal('last_used_at');

            if ($previous && now()->diffInSeconds($previous, true) < self::LAST_USED_THROTTLE_SECONDS) {
                // نتجاهل التحديث ونعيد القيم الأصلية حتى يبقى النموذج متسقاً
                $this->setRawAttributes($this->getRawOriginal(), true);

                return true;
            }
        }

        return parent::save($options);
    }
}
