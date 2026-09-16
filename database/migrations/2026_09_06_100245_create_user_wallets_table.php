<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wallet_provider_id')->constrained('wallet_providers')->onDelete('cascade');
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'user_id',
                'wallet_provider_id',
                'account_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};