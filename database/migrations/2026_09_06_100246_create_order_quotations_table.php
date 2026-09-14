<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('store_product_id')
                ->constrained('store_products')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('customer_target_price', 12, 2)->nullable();

            $table->decimal('offered_unit_price', 12, 2)->nullable();

            $table->decimal('total_price', 12, 2)->nullable();

            $table->enum('status', [
                'pending',
                'responded',
                'accepted',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->text('customer_notes')->nullable();

            $table->text('store_notes')->nullable();

            $table->timestamp('responded_at')->nullable();

            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};