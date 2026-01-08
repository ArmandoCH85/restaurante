<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('void_reason')->nullable()->after('payment_method');
            $table->timestamp('voided_at')->nullable()->after('void_reason');
            $table->foreignId('voided_by')->nullable()->constrained('users')->after('voided_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['void_reason', 'voided_at', 'voided_by']);
        });
    }
};
