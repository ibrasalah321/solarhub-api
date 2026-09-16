<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->decimal('subtotal', 12, 2);

            $table->enum('status', [
                'pending',
                'accepted',
                'shipped',
                'delivered',
                'completed',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamp('customer_confirmed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'order_id',
                'store_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_stores');
    }
};