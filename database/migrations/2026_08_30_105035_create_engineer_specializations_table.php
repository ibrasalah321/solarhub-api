<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_specializations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('engineer_id')
                ->constrained('engineer_profile')
                ->cascadeOnDelete();

            $table->foreignId('specialization_id')
                ->constrained('specializations')
                ->cascadeOnDelete();

            $table->unique([
                'engineer_id',
                'specialization_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_specializations');
    }
};