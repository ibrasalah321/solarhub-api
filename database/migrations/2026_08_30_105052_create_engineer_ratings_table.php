<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('engineer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id')->unique();
            $table->foreign('service_request_id')->references('id')->on('service_requests');
            $table->unsignedBigInteger('customer_id');

            $table->foreign('customer_id')
                ->references('id')->on('users');
            $table->unsignedBigInteger('engineer_id');

            $table->foreign('engineer_id')
                ->references('id')->on('engineer_profile');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->nullable()->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engineer_ratings');
    }
};
