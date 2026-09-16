<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('license_number', 100)->nullable();
            $table->unsignedInteger('years_of_experience')->nullable();
            $table->text('bio')->nullable();
            $table->string('cv_path', 255)->nullable();
            $table->string('profile_photo_path', 255)->nullable();
            $table->enum('approval_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_profile');
    }
};