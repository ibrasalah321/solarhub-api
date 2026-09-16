<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name');
            $table->string('support_phone');
            $table->string('support_email');
            $table->string('logo_path');
            $table->string('favicon_path')->nullable();
            $table->string('currency')->default('YER');
            $table->boolean('is_maintenance_mode')->default(false);
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};