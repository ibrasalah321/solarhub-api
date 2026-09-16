<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->restrictOnDelete();

            $table->string('title', 255);

            $table->string('model_number', 100)->nullable();

            $table->text('description')->nullable();

            $table->string('datasheet_file', 255)
                ->nullable()
                ->comment('الكتالوج الرسمي للمنتج');

            $table->boolean('is_active')
                ->default(true)
                ->comment('تحكم المشرف في إظهار المنتج');

            $table->timestamps();

            $table->unique([
                'brand_id',
                'model_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_products');
    }
};