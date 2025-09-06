<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AppSetting;

echo "=== CONFIGURACIÓN DE EMPRESA ===\n";
$empresaSettings = AppSetting::where('tab', 'Empresa')->get(['key', 'value']);
foreach ($empresaSettings as $setting) {
    echo "Empresa.{$setting->key} = {$setting->value}\n";
}

echo "\n=== CONFIGURACIÓN DE FACTURACIÓN ELECTRÓNICA ===\n";
$factSettings = AppSetting::where('tab', 'FacturacionElectronica')->get(['key', 'value']);
foreach ($factSettings as $setting) {
    $value = $setting->value;
    if (in_array($setting->key, ['sol_password', 'certificate_password'])) {
        $value = '[ENCRYPTED - ' . strlen($value) . ' chars]';
    }
    echo "FacturacionElectronica.{$setting->key} = {$value}\n";
}

echo "\n=== DATOS ESPECÍFICOS DEL LOG ===\n";
echo "RUC desde Empresa: " . AppSetting::getSetting('Empresa', 'ruc') . "\n";
echo "RUC desde FactElec: " . AppSetting::getSetting('FacturacionElectronica', 'ruc') . "\n";
echo "Usuario SOL: " . AppSetting::getSetting('FacturacionElectronica', 'sol_user') . "\n";
echo "Entorno: " . AppSetting::getSetting('FacturacionElectronica', 'environment') . "\n";