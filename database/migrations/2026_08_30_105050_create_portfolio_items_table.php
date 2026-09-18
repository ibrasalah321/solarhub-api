<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('engineer_id')
                ->constrained('engineer_profile')
                ->cascadeOnDelete();
            $table->foreignId('service_type_id')
                ->constrained('service_types')
                ->restrictOnDelete();
            $table->foreignId('governorate_id')
                ->nullable()
                ->constrained('governorates')
                ->nullOnDelete();
            $table->string('project_title', 150);
            $table->string('system_capacity', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('address_text', 255)->nullable();
            $table->string('location_coordinates')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
    }
};