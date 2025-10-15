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
        Schema::create('cash_register_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->onDelete('cascade');
            $table->string('concept');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_expenses');
    }
};
