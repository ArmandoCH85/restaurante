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
        // Obtener la primera caja registradora
        $firstCashRegister = DB::table('cash_registers')->first();

        if ($firstCashRegister) {
            // Actualizar todas las órdenes que no tienen cash_register_id
            DB::table('orders')
                ->whereNull('cash_register_id')
                ->update(['cash_register_id' => $firstCashRegister->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir esta migración ya que no queremos volver a dejar las órdenes sin cash_register_id
    }
};
