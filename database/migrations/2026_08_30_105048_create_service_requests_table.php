<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->restrictOnDelete();

            $table->foreignId('governorate_id')
                ->nullable()
                ->constrained('governorates')
                ->nullOnDelete();

            $table->string('system_capacity_estimate', 100)->nullable();

            $table->string('attachment_file', 255)->nullable();

            $table->text('location_details')->nullable();

            $table->string('location_coordinates')->nullable();

            $table->text('description');

            $table->enum('status', [
                'open_for_bids',
                'awarded',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('open_for_bids');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};