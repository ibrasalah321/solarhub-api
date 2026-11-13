<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * يقبل نقطة جغرافية بصيغة WKT فقط: POINT(lng lat) (اختيارياً مع SRID=4326;)
 * مع التحقق من النطاق: الطول بين -180 و180، والعرض بين -90 و90.
 *
 * أي نص آخر (مثل عنوان مكتوب) كان يصل لعمود geography فيفشل الاستعلام بخطأ 500 "invalid geometry".
 */
class GeographyPoint implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || ! preg_match('/^\s*(?:SRID=4326;)?\s*POINT\s*\(\s*(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s*\)\s*$/i', $value, $m)) {
            $fail('صيغة الإحداثيات غير صحيحة. اختر الموقع من الخريطة.');

            return;
        }

        [$lng, $lat] = [(float) $m[1], (float) $m[2]];

        if ($lng < -180 || $lng > 180 || $lat < -90 || $lat > 90) {
            $fail('الإحداثيات خارج النطاق المسموح.');
        }
    }
}
