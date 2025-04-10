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
        if (!Schema::hasTable('document_series')) {
            Schema::create('document_series', function (Blueprint $table) {
                $table->id();
                $table->string('document_type'); // 'invoice', 'receipt', 'sales_note'
                $table->string('series'); // 'F001', 'B001', 'NV001'
                $table->integer('current_number')->default(1);
                $table->boolean('active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();

                // Unique para evitar duplicados
                $table->unique(['document_type', 'series']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_series');
    }
};
