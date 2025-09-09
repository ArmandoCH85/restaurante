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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            
            // Relación con la factura original
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            
            // Datos del comprobante
            $table->string('series', 10); // Serie de la nota de crédito (ej: BC01)
            $table->string('number', 20); // Número correlativo
            $table->date('issue_date'); // Fecha de emisión
            
            // Motivo de la nota de crédito según catálogo SUNAT
            $table->string('motivo_codigo', 2); // 01=Anulación, 02=Error RUC, etc.
            $table->string('motivo_descripcion'); // Descripción del motivo
            
            // Montos (copiados de la factura original)
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            
            // Campos SUNAT
            $table->string('sunat_status', 20)->nullable(); // PENDIENTE, ACEPTADO, RECHAZADO
            $table->string('sunat_code', 10)->nullable(); // Código de respuesta SUNAT
            $table->text('sunat_description')->nullable(); // Descripción de respuesta SUNAT
            
            // Archivos generados
            $table->string('xml_path')->nullable(); // Ruta del XML firmado
            $table->string('cdr_path')->nullable(); // Ruta del CDR de SUNAT
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            
            // Control de envío
            $table->timestamp('sent_at')->nullable(); // Cuándo se envió a SUNAT
            $table->integer('retry_count')->default(0); // Número de reintentos
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Índices
            $table->index(['series', 'number']);
            $table->index('sunat_status');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
