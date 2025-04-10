<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migración está cubierta por la migración anterior
        // Si la migración anterior no pudo agregar la columna, intentemos de nuevo
        Schema::table('cash_registers', function (Blueprint $table) {
            // Verificar si la columna no existe para evitar error
            if (!Schema::hasColumn('cash_registers', 'status')) {
                $table->enum('status', ['open', 'closed'])->default('open')
                    ->after('difference');
            }

            // Actualizar registros existentes basados en is_active
            if (Schema::hasColumn('cash_registers', 'is_active')) {
                DB::statement('UPDATE cash_registers SET status = CASE WHEN is_active = 1 THEN "open" ELSE "closed" END');

                // Opcional: eliminar la columna is_active si ya no es necesaria
                // $table->dropColumn('is_active');
            }
        });
    }

    public function down(): void
    {
        // No hacer nada, ya que la columna se gestionará en la migración anterior
    }
};
