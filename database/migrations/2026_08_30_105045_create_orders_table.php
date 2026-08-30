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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('users');
            $table->unsignedBigInteger('delivery_governorate_id')->nullable();
            $table->foreign('delivery_governorate_id')->references('id')->on('governorates');
            $table->decimal('total_amount', 10, 2);
            $table->string('delivery_address', 255);
            $table->enum('payment_method', ["cash_on_delivery","bank_transfer","wallet"])->nullable();
            $table->enum('status', ["pending","processing","completed","cancelled"])->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('delivery_coordinates');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
