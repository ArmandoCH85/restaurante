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
        // Primero, actualizar todas las notas de venta para que tengan un valor temporal
        DB::table('invoices')
            ->where('invoice_type', 'sales_note')
            ->update(['sunat_status' => 'NO_APLICA']);

        // Modificar la columna para permitir NULL y agregar 'NO_APLICA' como opción
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('sunat_status', ['PENDIENTE', 'ENVIANDO', 'ACEPTADO', 'RECHAZADO', 'ERROR', 'NO_APLICA'])
                ->nullable()
                ->default(null)
                ->change();
        });

        // Actualizar las notas de venta para usar 'NO_APLICA'
        DB::table('invoices')
            ->where('invoice_type', 'sales_note')
            ->update(['sunat_status' => 'NO_APLICA']);

        // Actualizar facturas y boletas sin estado para que tengan 'PENDIENTE'
        DB::table('invoices')
            ->whereIn('invoice_type', ['invoice', 'receipt'])
            ->whereNull('sunat_status')
            ->update(['sunat_status' => 'PENDIENTE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('sunat_status', ['PENDIENTE', 'ENVIANDO', 'ACEPTADO', 'RECHAZADO', 'ERROR'])
                ->default('PENDIENTE')
                ->change();
        });
    }
};
