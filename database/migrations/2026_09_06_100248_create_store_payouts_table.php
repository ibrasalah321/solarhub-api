<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_payouts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_store_id')
                ->unique()
                ->constrained('order_stores')
                ->restrictOnDelete();

            $table->foreignId('user_wallet_id')
                ->nullable()
                ->constrained('user_wallets')
                ->nullOnDelete();

            $table->decimal('total_amount', 12, 2);

            $table->decimal('commission_rate', 5, 2);

            $table->decimal('commission_amount', 12, 2);

            $table->decimal('net_amount', 12, 2);

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
            ])->default('pending');

            $table->string('transfer_reference', 150)->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_payouts');
    }
};