<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DocumentSeries;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la tabla existe (puede no existir en entornos de testing)
        if (!Schema::hasTable('document_series')) {
            return;
        }

        // Verificar si ya existe una serie para notas de crédito
        $existingCreditNoteSeries = DocumentSeries::where('document_type', 'credit_note')->first();

        if (!$existingCreditNoteSeries) {
            // Crear serie por defecto para notas de crédito
            DocumentSeries::create([
                'document_type' => 'credit_note',
                'series' => 'FC001',
                'current_number' => 1,
                'active' => true,
                'description' => 'Serie por defecto para Notas de Crédito'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la tabla existe antes de intentar eliminar
        if (!Schema::hasTable('document_series')) {
            return;
        }

        // Eliminar la serie de notas de crédito creada por esta migración
        DocumentSeries::where('document_type', 'credit_note')
            ->where('series', 'FC001')
            ->where('description', 'Serie por defecto para Notas de Crédito')
            ->delete();
    }
};