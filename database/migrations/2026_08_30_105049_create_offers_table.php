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

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id');
            $table->foreign('service_request_id')->references('id')->on('service_requests');
            $table->unsignedBigInteger('engineer_id');

            $table->foreign('engineer_id')
                ->references('id')->on('engineer_profile');
            $table->decimal('proposed_cost', 10, 2);
            $table->unsignedInteger('execution_time_days');
            $table->text('technical_proposal');
            $table->string('proposal_file', 255)->nullable();
            $table->enum('status', ["pending","accepted","rejected"])->nullable();
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
        Schema::dropIfExists('offers');
    }
};
