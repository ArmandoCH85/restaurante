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
            $table->decimal('advance_payment_received', 12, 2)->default(0)->after('payment_amount')->comment('Anticipo recibido previamente');
            $table->text('advance_payment_notes')->nullable()->after('advance_payment_received')->comment('Notas del anticipo');
            $table->decimal('pending_balance', 12, 2)->default(0)->after('advance_payment_notes')->comment('Saldo pendiente después del anticipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['advance_payment_received', 'advance_payment_notes', 'pending_balance']);
        });
    }
};
