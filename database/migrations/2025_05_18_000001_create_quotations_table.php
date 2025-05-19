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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('user_id')->constrained();
            $table->date('issue_date');
            $table->date('valid_until');
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired', 'converted'])->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->enum('payment_terms', ['cash', 'credit_15', 'credit_30', 'credit_60'])->default('cash');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
