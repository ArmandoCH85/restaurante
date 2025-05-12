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
            $table->foreignId('approved_by')->nullable()->after('closed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approval_datetime')->nullable()->after('closing_datetime');
            $table->boolean('is_approved')->default(false)->after('is_active');
            $table->text('approval_notes')->nullable()->after('observations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approved_by',
                'approval_datetime',
                'is_approved',
                'approval_notes',
            ]);
        });
    }
};
