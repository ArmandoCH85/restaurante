<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Hacer document_type nullable
            $table->string('document_type', 10)->nullable()->change();
            
            // Hacer document_number nullable y quitar la restricción unique
            $table->dropUnique(['document_number']);
            $table->string('document_number', 15)->nullable()->change();
            
            // Agregar índice único compuesto que permita NULL
            // Solo será único cuando ambos campos tengan valor
            $table->unique(['document_type', 'document_number'], 'customers_document_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revertir cambios
            $table->dropUnique('customers_document_unique');
            $table->string('document_type', 10)->nullable(false)->change();
            $table->string('document_number', 15)->nullable(false)->unique()->change();
        });
    }
};
