<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo crear la tabla si no existe
        if (!Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('opened_by')->constrained('users');
                $table->foreignId('closed_by')->nullable()->constrained('users');
                $table->decimal('opening_amount', 12, 2)->default(0);
                $table->decimal('closing_amount', 12, 2)->nullable();
                $table->decimal('cash_sales', 12, 2)->nullable();
                $table->decimal('card_sales', 12, 2)->nullable();
                $table->decimal('other_sales', 12, 2)->nullable();
                $table->decimal('total_sales', 12, 2)->nullable();
                $table->decimal('expected_cash', 12, 2)->nullable();
                $table->decimal('actual_cash', 12, 2)->nullable();
                $table->decimal('difference', 12, 2)->nullable();
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->dateTime('opened_at');
                $table->dateTime('closed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Si la tabla existe pero falta la columna status, agregarla
        else if (!Schema::hasColumn('cash_registers', 'status')) {
            Schema::table('cash_registers', function (Blueprint $table) {
                $table->enum('status', ['open', 'closed'])->default('open')->after('difference');
            });
        }

        // Asegurarse de que exista la relación en la tabla payments
        if (Schema::hasTable('payments') && !Schema::hasColumn('payments', 'cash_register_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('cash_register_id')->nullable()->constrained()->after('order_id');
            });
        }
    }

    public function down(): void
    {
        // Eliminar la columna cash_register_id de la tabla payments
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'cash_register_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['cash_register_id']);
                $table->dropColumn('cash_register_id');
            });
        }

        // No eliminamos la tabla cash_registers aquí para evitar problemas
        // con la migración original que ya la creó
    }
};
