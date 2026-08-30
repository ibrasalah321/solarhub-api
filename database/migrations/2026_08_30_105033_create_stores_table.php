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

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('company_name', 150);
            $table->string('commercial_registry', 100)->nullable();
            $table->string('commercial_file_path', 255)->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->text('bio')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('company_logo_path');
            $table->enum('approval_status', [""]);
            $table->string('rejection_reason')->nullable();
            $table->enum('store_type', ["wholesaler","retailer","authorized_agent"]);
            $table->text('address_details');
            $table->timestamp('approved_at')->nullable();
            $table->string('location_coordinates');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
