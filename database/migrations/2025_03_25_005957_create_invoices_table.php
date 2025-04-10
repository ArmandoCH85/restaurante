<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->enum('invoice_type', ['receipt', 'invoice', 'credit_note', 'debit_note']);
            $table->string('series', 10);
            $table->string('number', 10);
            $table->date('issue_date');
            $table->foreignId('customer_id')->constrained();
            $table->decimal('taxable_amount', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('tax_authority_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->string('hash', 100)->nullable();
            $table->string('qr_code')->nullable();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->timestamps();

            $table->unique(['series', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
