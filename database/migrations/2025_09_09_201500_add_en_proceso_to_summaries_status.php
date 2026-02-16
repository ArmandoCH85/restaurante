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
        if (DB::getDriverName() !== 'sqlite') {
            // Modificar el enum para incluir 'EN_PROCESO'
            DB::statement("ALTER TABLE summaries MODIFY COLUMN status ENUM('PENDIENTE', 'EN_PROCESO', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ERROR') DEFAULT 'PENDIENTE'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Revertir al enum original
            DB::statement("ALTER TABLE summaries MODIFY COLUMN status ENUM('PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ERROR') DEFAULT 'PENDIENTE'");
        }
    }
};