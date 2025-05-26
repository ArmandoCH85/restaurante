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
        Schema::table('invoices', function (Blueprint $table) {
            // Verificar y agregar campos solo si no existen
            if (!Schema::hasColumn('invoices', 'xml_path')) {
                $table->string('xml_path', 500)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'pdf_path')) {
                $table->string('pdf_path', 500)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'cdr_path')) {
                $table->string('cdr_path', 500)->nullable();
            }

            // Estado SUNAT (usar el campo existente tax_authority_status o crear nuevo)
            if (!Schema::hasColumn('invoices', 'sunat_status')) {
                $table->enum('sunat_status', ['PENDIENTE', 'ENVIANDO', 'ACEPTADO', 'RECHAZADO', 'ERROR'])
                    ->default('PENDIENTE');
            }
            if (!Schema::hasColumn('invoices', 'sunat_code')) {
                $table->string('sunat_code', 10)->nullable();
            }
            if (!Schema::hasColumn('invoices', 'sunat_description')) {
                $table->text('sunat_description')->nullable();
            }

            // Hash del XML firmado para integridad
            if (!Schema::hasColumn('invoices', 'hash_sign')) {
                $table->string('hash_sign', 255)->nullable();
            }

            // Timestamp de envío
            if (!Schema::hasColumn('invoices', 'sent_at')) {
                $table->timestamp('sent_at')->nullable();
            }

            // Empleado que emitió la factura
            if (!Schema::hasColumn('invoices', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'xml_path',
                'pdf_path',
                'cdr_path',
                'sunat_code',
                'sunat_description',
                'hash_sign',
                'sent_at'
            ]);

            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
