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
        Schema::disableForeignKeyConstraints();

        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_product_id');
            $table->foreign('master_product_id')->references('id')->on('master_products');
            $table->unsignedBigInteger('store_id');
            $table->foreign('store_id')->references('id')->on('stores');
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->foreign('governorate_id')->references('id')->on('governorates');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity');
            $table->unsignedInteger('min_order_qty')->default(1);
            $table->string('warranty_period', 100)->nullable();
            $table->boolean('is_available')->nullable()->default(true);
            $table->enum('status', ["active","inactive","out_of_stock"])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['master_product_id', 'store_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_products');
    }
};
