<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('sale_price', 10, 2);
            $table->decimal('current_cost', 10, 2)->default(0);
            $table->enum('product_type', ['ingredient', 'sale_item', 'both']);
            $table->foreignId('category_id')->constrained('product_categories');
            $table->boolean('active')->default(true);
            $table->boolean('has_recipe')->default(false);
            $table->string('image_path')->nullable();
            $table->boolean('available')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
