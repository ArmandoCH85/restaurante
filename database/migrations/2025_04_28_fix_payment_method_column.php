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
        // Modificar la columna payment_method para que sea VARCHAR en lugar de ENUM
        DB::statement('ALTER TABLE payments MODIFY payment_method VARCHAR(30) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos a ENUM para evitar problemas con datos existentes
    }
};
