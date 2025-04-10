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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('unit_of_measure', 20); // gramos, litros, unidades, etc.
            $table->decimal('min_stock', 10, 3)->default(0);
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('current_cost', 10, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
