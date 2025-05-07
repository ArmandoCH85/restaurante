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
            // Verificar si las columnas no existen antes de agregarlas
            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('order_id');
            }

            if (!Schema::hasColumn('invoices', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_method');
            }

            if (!Schema::hasColumn('invoices', 'client_name')) {
                $table->string('client_name')->nullable()->after('payment_amount');
            }

            if (!Schema::hasColumn('invoices', 'client_document')) {
                $table->string('client_document')->nullable()->after('client_name');
            }

            if (!Schema::hasColumn('invoices', 'client_address')) {
                $table->string('client_address')->nullable()->after('client_document');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Eliminar las columnas si existen
            $columns = [
                'payment_method',
                'payment_amount',
                'client_name',
                'client_document',
                'client_address'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
