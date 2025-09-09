<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campos de credenciales QPSE a la configuración de facturación electrónica
        $settings = [
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'qpse_username',
                'value' => '',
                'default' => ''
            ],
            [
                'tab' => 'FacturacionElectronica',
                'key' => 'qpse_password',
                'value' => '',
                'default' => ''
            ]
        ];

        foreach ($settings as $setting) {
            // Verificar si el setting ya existe
            $exists = DB::table('app_settings')
                ->where('tab', $setting['tab'])
                ->where('key', $setting['key'])
                ->exists();

            if (!$exists) {
                DB::table('app_settings')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'tab' => $setting['tab'],
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'default' => $setting['default'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los campos de credenciales QPSE
        DB::table('app_settings')
            ->where('tab', 'FacturacionElectronica')
            ->whereIn('key', ['qpse_username', 'qpse_password'])
            ->delete();
    }
};