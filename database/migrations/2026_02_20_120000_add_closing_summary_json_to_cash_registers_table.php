<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cash_registers', 'closing_summary_json')) {
            Schema::table('cash_registers', function (Blueprint $table) {
                $table->json('closing_summary_json')
                    ->nullable()
                    ->after('observations')
                    ->comment('Resumen estructurado de cierre de caja para UI y auditoria');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cash_registers', 'closing_summary_json')) {
            Schema::table('cash_registers', function (Blueprint $table) {
                $table->dropColumn('closing_summary_json');
            });
        }
    }
};
