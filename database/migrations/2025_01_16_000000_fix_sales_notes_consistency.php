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
        // 1. Convertir todas las facturas con invoice_type='sales_note' a la forma estándar
        // sales_note -> invoice_type='receipt' + sunat_status=null
        DB::table('invoices')
            ->where('invoice_type', 'sales_note')
            ->update([
                'invoice_type' => 'receipt',
                'sunat_status' => null
            ]);

        // 2. Asegurar que todas las facturas tipo 'receipt' con series de Nota de Venta (NV) 
        // tengan sunat_status=null
        DB::table('invoices')
            ->where('invoice_type', 'receipt')
            ->where('series', 'like', 'NV%')
            ->update(['sunat_status' => null]);

        // 3. Asegurar que todas las facturas y boletas que no son Notas de Venta 
        // tengan un sunat_status válido
        DB::table('invoices')
            ->where('invoice_type', 'receipt')
            ->where('series', 'not like', 'NV%')
            ->whereNull('sunat_status')
            ->update(['sunat_status' => 'PENDIENTE']);

        DB::table('invoices')
            ->where('invoice_type', 'invoice')
            ->whereNull('sunat_status')
            ->update(['sunat_status' => 'PENDIENTE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir para mantener consistencia
        // La reversión podría causar problemas de datos
    }
};