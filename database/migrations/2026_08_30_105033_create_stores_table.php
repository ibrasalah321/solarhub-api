<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('company_name', 150);

            $table->string('commercial_registry', 100)->nullable();

            $table->string('commercial_file_path', 255)->nullable();

            $table->string('tax_number', 100)->nullable();

            $table->text('bio')->nullable();

            $table->string('company_logo_path', 255)->nullable();

            $table->string('whatsapp_number', 30)->nullable();

            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->string('rejection_reason', 255)->nullable();

            $table->enum('store_type', [
                'wholesaler',
                'retailer',
                'authorized_agent',
            ]);

            $table->text('address_details');

            $table->string('location_coordinates')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};