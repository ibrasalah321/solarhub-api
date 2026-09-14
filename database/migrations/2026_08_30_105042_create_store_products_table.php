<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('master_product_id')
                ->constrained('master_products')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignId('governorate_id')
                ->nullable()
                ->constrained('governorates')
                ->nullOnDelete();

            $table->decimal('price', 12, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);

            $table->unsignedInteger('min_order_qty')->default(1);

            $table->string('warranty_period', 100)->nullable();

            $table->enum('status', [
                'active',
                'inactive',
                'out_of_stock',
            ])->default('active');

            $table->timestamps();

            $table->unique([
                'master_product_id',
                'store_id',
                'governorate_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};