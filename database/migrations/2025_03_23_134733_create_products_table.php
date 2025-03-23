<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->text('tags')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('variant_1_name')->nullable();
            $table->string('variant_1_value')->nullable();
            $table->string('variant_2_name')->nullable();
            $table->string('variant_2_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
