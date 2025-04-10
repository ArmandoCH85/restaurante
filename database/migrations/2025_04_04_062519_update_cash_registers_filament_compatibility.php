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
        // Agregar nuevas columnas para el cálculo de ventas
        Schema::table('cash_registers', function (Blueprint $table) {
            // No se agrega 'status' ya que ya existe 'is_active' que cumple la misma función

            // Columnas para detalles de ventas por categoría
            $table->decimal('cash_sales', 12, 2)->nullable()->after('actual_amount');
            $table->decimal('card_sales', 12, 2)->nullable()->after('cash_sales');
            $table->decimal('other_sales', 12, 2)->nullable()->after('card_sales');
            $table->decimal('total_sales', 12, 2)->nullable()->after('other_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn([
                'cash_sales',
                'card_sales',
                'other_sales',
                'total_sales',
            ]);
        });
    }
};
