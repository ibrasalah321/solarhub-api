<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('governorate_id')
                ->nullable()
                ->constrained('governorates')
                ->nullOnDelete();

            $table->string('name', 150);

            $table->string('email', 150)->unique();

            $table->string('phone', 30)->unique();

            $table->string('password');

            $table->enum('status', [
                'active',
                'inactive',
                'suspended',
            ])->default('active');

            $table->string('default_coordinates')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};