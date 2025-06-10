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
        // Actualizar facturas y boletas existentes que no tienen sunat_status establecido
        DB::table('invoices')
            ->whereIn('invoice_type', ['invoice', 'receipt'])
            ->whereNull('sunat_status')
            ->update(['sunat_status' => 'PENDIENTE']);

        // Actualizar notas de venta para que no tengan sunat_status
        DB::table('invoices')
            ->where('invoice_type', 'sales_note')
            ->orWhere('series', 'like', 'NV%')
            ->update(['sunat_status' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir los cambios para mantener consistencia
    }
};
