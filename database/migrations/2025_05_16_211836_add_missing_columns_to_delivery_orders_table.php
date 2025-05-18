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
        // Verificar si las columnas ya existen antes de intentar agregarlas
        if (!Schema::hasColumn('delivery_orders', 'delivery_latitude')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_references');
            });
        }

        if (!Schema::hasColumn('delivery_orders', 'delivery_longitude')) {
            Schema::table('delivery_orders', function (Blueprint $table) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_orders', 'delivery_latitude')) {
                $table->dropColumn('delivery_latitude');
            }
            
            if (Schema::hasColumn('delivery_orders', 'delivery_longitude')) {
                $table->dropColumn('delivery_longitude');
            }
        });
    }
};
