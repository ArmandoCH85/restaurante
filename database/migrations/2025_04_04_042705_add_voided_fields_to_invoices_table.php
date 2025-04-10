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
        // Verificar si la tabla ya existe
        if (Schema::hasTable('invoices')) {
            // Verificar si la columna tax_authority_status existe
            if (Schema::hasColumn('invoices', 'tax_authority_status')) {
                // Añadir valor 'voided' al enum
                DB::statement("ALTER TABLE invoices MODIFY COLUMN tax_authority_status ENUM('pending', 'accepted', 'rejected', 'voided') NOT NULL DEFAULT 'pending'");
            }

            // Añadir campos solo si no existen
            if (!Schema::hasColumn('invoices', 'voided_reason')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->string('voided_reason')->nullable()->after('tax_authority_status');
                });
            }

            if (!Schema::hasColumn('invoices', 'voided_date')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->date('voided_date')->nullable()->after('voided_reason');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar si la tabla existe
        if (Schema::hasTable('invoices')) {
            // Eliminar columnas solo si existen
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'voided_reason')) {
                    $table->dropColumn('voided_reason');
                }

                if (Schema::hasColumn('invoices', 'voided_date')) {
                    $table->dropColumn('voided_date');
                }
            });

            // Restaurar ENUM solo si existe
            if (Schema::hasColumn('invoices', 'tax_authority_status')) {
                DB::statement("ALTER TABLE invoices MODIFY COLUMN tax_authority_status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
            }
        }
    }
};
