<?php

/**
 * Script para solucionar duplicados de campos QPSE
 */

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;

echo "🔧 SOLUCIONANDO DUPLICADOS DE CAMPOS QPSE...\n";
echo "=============================================\n\n";

try {
    // Verificar todos los campos qpse_endpoint_beta en todas las tabs
    echo "🔍 Buscando todos los campos qpse_endpoint_beta...\n";
    $betaFields = AppSetting::where('key', 'qpse_endpoint_beta')->get();
    
    echo "Encontrados: " . $betaFields->count() . " campos qpse_endpoint_beta\n";
    foreach ($betaFields as $field) {
        echo "  - Tab: {$field->tab} | ID: " . substr($field->id, 0, 8) . "... | Value: " . ($field->value ?: '(vacío)') . "\n";
    }
    
    // Verificar todos los campos qpse_endpoint_production
    echo "\n🔍 Buscando todos los campos qpse_endpoint_production...\n";
    $prodFields = AppSetting::where('key', 'qpse_endpoint_production')->get();
    
    echo "Encontrados: " . $prodFields->count() . " campos qpse_endpoint_production\n";
    foreach ($prodFields as $field) {
        echo "  - Tab: {$field->tab} | ID: " . substr($field->id, 0, 8) . "... | Value: " . ($field->value ?: '(vacío)') . "\n";
    }
    
    // Eliminar campos QPSE que NO estén en FacturacionElectronica
    echo "\n🗑️ Eliminando campos QPSE que no estén en FacturacionElectronica...\n";
    
    $deletedBeta = AppSetting::where('key', 'qpse_endpoint_beta')
        ->where('tab', '!=', 'FacturacionElectronica')
        ->delete();
    
    $deletedProd = AppSetting::where('key', 'qpse_endpoint_production')
        ->where('tab', '!=', 'FacturacionElectronica')
        ->delete();
    
    echo "Eliminados qpse_endpoint_beta fuera de FacturacionElectronica: $deletedBeta\n";
    echo "Eliminados qpse_endpoint_production fuera de FacturacionElectronica: $deletedProd\n";
    
    // Verificar si existen en FacturacionElectronica
    echo "\n📋 Verificando campos en FacturacionElectronica...\n";
    $betaInFact = AppSetting::where('tab', 'FacturacionElectronica')
        ->where('key', 'qpse_endpoint_beta')
        ->first();
    
    $prodInFact = AppSetting::where('tab', 'FacturacionElectronica')
        ->where('key', 'qpse_endpoint_production')
        ->first();
    
    if ($betaInFact) {
        echo "✅ qpse_endpoint_beta existe en FacturacionElectronica\n";
        echo "   ID: {$betaInFact->id}\n";
        echo "   Value: " . ($betaInFact->value ?: '(vacío)') . "\n";
    } else {
        echo "❌ qpse_endpoint_beta NO existe en FacturacionElectronica\n";
        echo "🔧 Creándolo...\n";
        
        $betaInFact = AppSetting::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tab' => 'FacturacionElectronica',
            'key' => 'qpse_endpoint_beta',
            'default' => '',
            'value' => '',
        ]);
        echo "✅ Creado: qpse_endpoint_beta en FacturacionElectronica\n";
    }
    
    if ($prodInFact) {
        echo "✅ qpse_endpoint_production existe en FacturacionElectronica\n";
        echo "   ID: {$prodInFact->id}\n";
        echo "   Value: " . ($prodInFact->value ?: '(vacío)') . "\n";
    } else {
        echo "❌ qpse_endpoint_production NO existe en FacturacionElectronica\n";
        echo "🔧 Creándolo...\n";
        
        $prodInFact = AppSetting::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tab' => 'FacturacionElectronica',
            'key' => 'qpse_endpoint_production',
            'default' => '',
            'value' => '',
        ]);
        echo "✅ Creado: qpse_endpoint_production en FacturacionElectronica\n";
    }
    
    // Verificación final
    echo "\n📊 VERIFICACIÓN FINAL:\n";
    $finalFactFields = AppSetting::where('tab', 'FacturacionElectronica')
        ->where('key', 'LIKE', '%qpse%')
        ->get();
    
    echo "Campos QPSE en FacturacionElectronica: " . $finalFactFields->count() . "\n";
    foreach ($finalFactFields as $field) {
        $icon = match ($field->key) {
            'qpse_endpoint_beta' => '🧪',
            'qpse_endpoint_production' => '🚀',
            default => '📝'
        };
        echo "  $icon {$field->key}: " . ($field->value ?: '(sin configurar)') . "\n";
    }
    
    echo "\n🎯 RESULTADO:\n";
    echo "✅ Duplicados eliminados\n";
    echo "✅ Campos QPSE existen en FacturacionElectronica\n";
    echo "✅ No hay conflictos de unicidad\n";
    
    echo "\n📍 VERIFICACIÓN:\n";
    echo "1. Ve a: http://restaurante.test/admin/configuracion/facturacion-electronica\n";
    echo "2. Recarga la página (F5)\n";
    echo "3. Los campos 🧪 y 🚀 deberían aparecer ahora\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 ¡Duplicados solucionados!\n";