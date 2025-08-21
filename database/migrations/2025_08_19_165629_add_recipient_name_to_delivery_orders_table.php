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
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_orders', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('delivery_references');
            }
            
            if (!Schema::hasColumn('delivery_orders', 'recipient_phone')) {
                $table->string('recipient_phone')->nullable()->after('recipient_name');
            }
            
            if (!Schema::hasColumn('delivery_orders', 'recipient_address')) {
                $table->text('recipient_address')->nullable()->after('recipient_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_orders', 'recipient_name')) {
                $table->dropColumn('recipient_name');
            }
            
            if (Schema::hasColumn('delivery_orders', 'recipient_phone')) {
                $table->dropColumn('recipient_phone');
            }
            
            if (Schema::hasColumn('delivery_orders', 'recipient_address')) {
                $table->dropColumn('recipient_address');
            }
        });
    }
};