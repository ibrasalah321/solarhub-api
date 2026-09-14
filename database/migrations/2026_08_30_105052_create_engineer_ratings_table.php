<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineer_ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_id')
                ->unique()
                ->constrained('service_requests')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('engineer_id')
                ->constrained('engineer_profile')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('comment')->nullable();

            $table->boolean('is_approved')->default(true);

            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE engineer_ratings
             ADD CONSTRAINT engineer_ratings_rating_check
             CHECK (rating BETWEEN 1 AND 5)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('engineer_ratings');
    }
};