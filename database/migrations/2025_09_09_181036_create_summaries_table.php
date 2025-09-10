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
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->string('correlativo')->unique(); // RC-YYYYMMDD-001
            $table->date('fecha_referencia'); // Fecha de las boletas
            $table->date('fecha_generacion'); // Fecha de generación del resumen
            $table->string('ticket', 20)->nullable(); // Ticket de SUNAT
            $table->enum('status', ['PENDIENTE', 'ENVIADO', 'ACEPTADO', 'RECHAZADO', 'ERROR'])->default('PENDIENTE');
            $table->string('sunat_code', 10)->nullable(); // Código de respuesta SUNAT
            $table->text('sunat_description')->nullable(); // Descripción de respuesta SUNAT
            $table->integer('receipts_count')->default(0); // Cantidad de boletas incluidas
            $table->decimal('total_amount', 10, 2)->default(0); // Monto total del resumen
            $table->string('xml_path')->nullable(); // Ruta del archivo XML
            $table->string('cdr_path')->nullable(); // Ruta del CDR
            $table->json('receipts_data')->nullable(); // Datos de las boletas incluidas
            $table->text('error_message')->nullable(); // Mensaje de error si aplica
            $table->integer('processing_time_ms')->nullable(); // Tiempo de procesamiento
            $table->timestamps();
            
            // Índices
            $table->index(['fecha_referencia', 'status']);
            $table->index('ticket');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
