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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('advance_payment', 12, 2)->default(0)->after('total')->comment('Anticipo o dinero a cuenta');
            $table->text('advance_payment_notes')->nullable()->after('advance_payment')->comment('Notas sobre el anticipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['advance_payment', 'advance_payment_notes']);
        });
    }
};
