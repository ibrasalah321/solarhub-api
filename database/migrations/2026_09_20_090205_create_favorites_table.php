<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            // المستخدم صاحب المفضلة
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // منتج المتجر المفضل
            $table->foreignId('store_product_id')
                ->constrained('store_products')
                ->cascadeOnDelete();

            $table->timestamps();

            // منع تكرار إضافة نفس المنتج إلى مفضلة المستخدم أكثر من مرة
            $table->unique(['user_id', 'store_product_id'], 'user_store_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};