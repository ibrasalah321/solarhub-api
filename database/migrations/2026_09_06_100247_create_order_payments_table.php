<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->foreignId('wallet_provider_id')
                ->nullable()
                ->constrained('wallet_providers')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('transaction_reference', 150)->nullable();

            $table->string('receipt_image', 255)->nullable();

            $table->enum('status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('paid_at')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};