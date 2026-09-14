<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_request_id')
                ->constrained('service_requests')
                ->cascadeOnDelete();

            $table->foreignId('engineer_id')
                ->constrained('engineer_profile')
                ->restrictOnDelete();

            $table->decimal('proposed_cost', 12, 2);

            $table->unsignedInteger('execution_time_days');

            $table->text('technical_proposal');

            $table->string('proposal_file', 255)->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
            ])->default('pending');

            $table->timestamps();

            $table->unique([
                'service_request_id',
                'engineer_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};