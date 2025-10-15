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
            $table->decimal('total_expenses', 12, 2)->nullable()->after('total_sales');
            $table->text('expenses_notes')->nullable()->after('total_expenses');
            $table->enum('expense_method', ['detailed', 'total'])->nullable()->after('expenses_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['total_expenses', 'expenses_notes', 'expense_method']);
        });
    }
};
