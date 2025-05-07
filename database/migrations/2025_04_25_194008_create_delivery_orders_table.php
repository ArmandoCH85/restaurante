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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->text('delivery_address');
            $table->text('delivery_references')->nullable();
            $table->foreignId('delivery_person_id')->nullable()->constrained('employees');
            $table->enum('status', ['pending', 'assigned', 'in_transit', 'delivered', 'cancelled'])->default('pending');
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->timestamp('actual_delivery_time')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
