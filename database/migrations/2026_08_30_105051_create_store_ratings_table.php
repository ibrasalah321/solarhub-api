<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_store_id')
                ->unique()
                ->constrained('order_stores')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('comment')->nullable();

            $table->boolean('is_approved')->default(true);

            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE store_ratings
             ADD CONSTRAINT store_ratings_rating_check
             CHECK (rating BETWEEN 1 AND 5)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('store_ratings');
    }
};