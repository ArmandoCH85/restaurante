<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero cambiamos el tipo de la columna a string para evitar restricciones de enum
        Schema::table('payments', function (Blueprint $table) {
            // Cambiar el tipo de columna de enum a string
            DB::statement('ALTER TABLE payments MODIFY payment_method VARCHAR(20) NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a enum con los valores originales
        Schema::table('payments', function (Blueprint $table) {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'digital_wallet') NOT NULL");
        });
    }
};
