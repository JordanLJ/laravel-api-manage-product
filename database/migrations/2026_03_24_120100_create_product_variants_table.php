<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('sku')->unique();
            $table->json('attributes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('promo_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->integer('stock')->default(0);
            $table->string('status')->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
