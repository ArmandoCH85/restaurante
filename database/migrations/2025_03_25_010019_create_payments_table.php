<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'digital_wallet']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->dateTime('payment_datetime');
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

