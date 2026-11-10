<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;

/**
 * اتصال PostgreSQL يدعم PDO::ATTR_EMULATE_PREPARES.
 *
 * Laravel يحوّل القيم المنطقية إلى 0/1. مع الـ prepares الحقيقية يستنتج PostgreSQL النوع من العمود،
 * أما مع الـ prepares المحاكاة فتُدرج القيمة كرقم صريح فيفشل الاستعلام بخطأ
 * "operator does not exist: boolean = integer". لذلك نرسلها كنص 'true'/'false'.
 *
 * الـ prepares المحاكاة ضرورية مع Supabase transaction pooler (المنفذ 6543):
 * تقلل رحلات الشبكة لكل استعلام من ثلاث إلى واحدة، وتتجنب خطأ
 * "prepared statement ... does not exist".
 */
class PostgresConnection extends BasePostgresConnection
{
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return parent::prepareBindings($bindings);
    }
}
