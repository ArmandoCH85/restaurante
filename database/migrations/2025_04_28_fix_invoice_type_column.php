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
        // Verificar si la columna invoice_type es un ENUM
        $columnType = DB::select("SHOW COLUMNS FROM invoices WHERE Field = 'invoice_type'")[0]->Type;
        
        if (strpos($columnType, 'enum') !== false) {
            // Modificar la columna invoice_type para que sea VARCHAR y pueda aceptar 'sales_note'
            DB::statement("ALTER TABLE invoices MODIFY COLUMN invoice_type VARCHAR(20) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos a ENUM para evitar problemas con datos existentes
    }
};
