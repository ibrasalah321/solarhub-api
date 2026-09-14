<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('master_products')
                ->cascadeOnDelete();

            $table->string('name', 100);

            $table->string('value', 255);

            $table->string('unit', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};