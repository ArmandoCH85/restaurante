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
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('sunat_sent_datetime')->nullable();
            $table->string('sunat_response_code', 10)->nullable();
            $table->text('sunat_response_description')->nullable();
            $table->string('sunat_cdr_path', 255)->nullable();
            $table->string('sunat_xml_path', 255)->nullable();
            $table->string('sunat_pdf_path', 255)->nullable();
            $table->timestamp('sunat_last_check_datetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'sunat_sent_datetime',
                'sunat_response_code',
                'sunat_response_description',
                'sunat_cdr_path',
                'sunat_xml_path',
                'sunat_pdf_path',
                'sunat_last_check_datetime'
            ]);
        });
    }
};
