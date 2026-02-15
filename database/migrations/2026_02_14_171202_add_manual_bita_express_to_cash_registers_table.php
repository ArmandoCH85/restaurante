<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            // Agregar columna manual_bita_express después de manual_pedidos_ya
            $table->decimal('manual_bita_express', 10, 2)->default(0)->after('manual_pedidos_ya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            // Eliminar columna manual_bita_express
            $table->dropColumn('manual_bita_express');
        });
    }
};
