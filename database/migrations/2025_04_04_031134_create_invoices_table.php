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
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained();
                $table->string('invoice_type'); // sales_note, receipt, invoice
                $table->string('series');
                $table->string('number');
                $table->string('client_name')->nullable();
                $table->string('client_document')->nullable();
                $table->string('client_address')->nullable();
                $table->decimal('subtotal', 10, 2);
                $table->decimal('tax_percent', 5, 2);
                $table->decimal('tax_amount', 10, 2);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('total', 10, 2);
                $table->string('payment_method'); // cash, card, transfer, yape, plin
                $table->decimal('payment_amount', 10, 2);
                $table->string('status'); // completed, voided
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
