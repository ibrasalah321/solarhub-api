<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ربط طلب عرض السعر المقبول بطلب الشراء الذي أُنشئ منه بالسعر المتفق عليه.
 * إضافي فقط: عمود قابل لـ null، ولا يغيّر بيانات موجودة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('status')
                ->constrained('orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
