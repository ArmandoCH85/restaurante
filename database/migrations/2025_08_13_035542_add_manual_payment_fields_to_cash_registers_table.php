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
        Schema::table('cash_registers', function (Blueprint $table) {
            // Campos de conteo manual de métodos de pago digitales (decimales)
            $table->decimal('manual_yape', 12, 2)->nullable()->after('approval_notes');
            $table->decimal('manual_plin', 12, 2)->nullable()->after('manual_yape');
            $table->decimal('manual_card', 12, 2)->nullable()->after('manual_plin');
            $table->decimal('manual_didi', 12, 2)->nullable()->after('manual_card');
            $table->decimal('manual_pedidos_ya', 12, 2)->nullable()->after('manual_didi');
            $table->decimal('manual_otros', 12, 2)->nullable()->after('manual_pedidos_ya');
            
            // Campos de billetes (enteros - cantidad de billetes)
            $table->integer('bill_200')->nullable()->default(0)->after('manual_otros');
            $table->integer('bill_100')->nullable()->default(0)->after('bill_200');
            $table->integer('bill_50')->nullable()->default(0)->after('bill_100');
            $table->integer('bill_20')->nullable()->default(0)->after('bill_50');
            $table->integer('bill_10')->nullable()->default(0)->after('bill_20');
            
            // Campos de monedas (enteros - cantidad de monedas)
            $table->integer('coin_5')->nullable()->default(0)->after('bill_10');
            $table->integer('coin_2')->nullable()->default(0)->after('coin_5');
            $table->integer('coin_1')->nullable()->default(0)->after('coin_2');
            $table->integer('coin_050')->nullable()->default(0)->after('coin_1');
            $table->integer('coin_020')->nullable()->default(0)->after('coin_050');
            $table->integer('coin_010')->nullable()->default(0)->after('coin_020');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn([
                // Campos de conteo manual de métodos de pago
                'manual_yape',
                'manual_plin',
                'manual_card',
                'manual_didi',
                'manual_pedidos_ya',
                'manual_otros',
                
                // Campos de billetes
                'bill_200',
                'bill_100',
                'bill_50',
                'bill_20',
                'bill_10',
                
                // Campos de monedas
                'coin_5',
                'coin_2',
                'coin_1',
                'coin_050',
                'coin_020',
                'coin_010',
            ]);
        });
    }
};