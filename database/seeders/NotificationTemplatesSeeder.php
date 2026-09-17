<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code'       => 'order_created',
                'title'      => 'طلب شراء جديد',
                'body'       => 'تم استلام طلبكم برقم {order_number} بنجاح.',
                'type'       => 'order',
                'variables'  => json_encode(['order_number']),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'quote_received',
                'title'      => 'رد على طلب التسعير',
                'body'       => 'قام المتجر بتقديم عرض سعر لطلبكم.',
                'type'       => 'quote',
                'variables'  => json_encode(['quote_id']),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code'       => 'payment_verified',
                'title'      => 'تأكيد الدفع',
                'body'       => 'تم التحقق من حوالة الدفع بنجاح لطلبكم {order_number}.',
                'type'       => 'payment',
                'variables'  => json_encode(['order_number']),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $tmpl) {
            DB::table('notification_templates')->updateOrInsert(
                ['code' => $tmpl['code']],
                $tmpl
            );
        }
    }
}
