<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('engineer_id')
                ->constrained('engineer_profile')
                ->cascadeOnDelete();

            $table->string('file_path', 255);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_certificates');
    }
};