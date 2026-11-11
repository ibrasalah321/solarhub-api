<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * دورة حياة طلب الخدمة بعد قبول العرض:
 * awarded → (المهندس: start) in_progress → (المهندس: mark-done) engineer_completed_at
 *        → (العميل: confirm-completion) completed → (العميل) تقييم المهندس.
 *
 * إضافية فقط: أعمدة تواريخ قابلة لـ null، وقوالب إشعارات تُضاف إن لم تكن موجودة
 * (لا نعدّل قوالب موجودة قد يكون المدير غيّر نصوصها).
 */
return new class extends Migration
{
    private const TEMPLATES = [
        ['code' => 'service_started_customer', 'title' => 'بدأ تنفيذ طلب الخدمة', 'body' => 'بدأ المهندس {{engineer_name}} تنفيذ طلب الخدمة رقم {{service_request_id}}.', 'variables' => ['engineer_name', 'service_request_id']],
        ['code' => 'service_marked_done_customer', 'title' => 'المهندس أنهى العمل', 'body' => 'أبلغ المهندس {{engineer_name}} بإنهاء العمل في طلب الخدمة رقم {{service_request_id}}. يرجى تأكيد الاكتمال.', 'variables' => ['engineer_name', 'service_request_id']],
        ['code' => 'service_completed_engineer', 'title' => 'تم تأكيد اكتمال المشروع', 'body' => 'أكد العميل {{customer_name}} اكتمال طلب الخدمة رقم {{service_request_id}}.', 'variables' => ['customer_name', 'service_request_id']],
        ['code' => 'engineer_rated_engineer', 'title' => 'تقييم جديد', 'body' => 'قيّم العميل {{customer_name}} عملك في طلب الخدمة رقم {{service_request_id}} بـ {{rating}} من 5.', 'variables' => ['customer_name', 'service_request_id', 'rating']],
    ];

    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'engineer_completed_at')) {
                $table->timestamp('engineer_completed_at')->nullable();
            }
            if (! Schema::hasColumn('service_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });

        foreach (self::TEMPLATES as $template) {
            if (DB::table('notification_templates')->where('code', $template['code'])->exists()) {
                continue;
            }

            DB::table('notification_templates')->insert([
                'code' => $template['code'],
                'type' => 'service_request',
                'title' => $template['title'],
                'body' => $template['body'],
                'variables' => json_encode($template['variables'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('notification_templates')
            ->whereIn('code', array_column(self::TEMPLATES, 'code'))
            ->delete();

        Schema::table('service_requests', function (Blueprint $table) {
            foreach (['started_at', 'engineer_completed_at', 'completed_at'] as $column) {
                if (Schema::hasColumn('service_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
