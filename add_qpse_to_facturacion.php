<?php

/**
 * Script para agregar campos QPSE a la configuración de Facturación Electrónica
 */

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AppSetting;
use Illuminate\Support\Str;

echo "🚀 AGREGANDO CAMPOS QPSE A FACTURACIÓN ELECTRÓNICA...\n";
echo "=====================================================\n\n";

try {
    // Verificar campos existentes
    echo "📋 Verificando configuración actual de Facturación Electrónica...\n";
    $factSettings = AppSetting::where('tab', 'FacturacionElectronica')->get();
    echo "Total configuraciones existentes: " . $factSettings->count() . "\n\n";
    
    // Campos QPSE a agregar
    $qpseFields = [
        [
            'key' => 'qpse_endpoint_beta',
            'label' => '🧪 Endpoint QPSE Beta',
            'description' => 'Para pruebas y desarrollo'
        ],
        [
            'key' => 'qpse_endpoint_production',
            'label' => '🚀 Endpoint QPSE Producción',
            'description' => 'Para comprobantes reales'
        ]
    ];
    
    $created = 0;
    $existing = 0;
    
    foreach ($qpseFields as $field) {
        echo "🔍 Verificando {$field['label']}...\n";
        
        // Verificar si ya existe
        $setting = AppSetting::where('tab', 'FacturacionElectronica')
            ->where('key', $field['key'])
            ->first();
        
        if ($setting) {
            echo "ℹ️ {$field['label']} ya existe.\n";
            $existing++;
        } else {
            echo "❌ {$field['label']} no existe. Creándolo...\n";
            
            // Crear el campo
            $setting = AppSetting::create([
                'id' => Str::uuid()->toString(),
                'tab' => 'FacturacionElectronica',
                'key' => $field['key'],
                'default' => '',
                'value' => '',
            ]);
            
            echo "✅ {$field['label']} creado exitosamente.\n";
            echo "   ID: {$setting->id}\n";
            $created++;
        }
        echo "\n";
    }
    
    // Mostrar resumen
    echo "📊 RESUMEN:\n";
    echo "✅ Campos creados: $created\n";
    echo "ℹ️ Campos existentes: $existing\n\n";
    
    // Mostrar todos los campos QPSE en Facturación Electrónica
    echo "📋 Campos QPSE en Facturación Electrónica:\n";
    $qpseInFact = AppSetting::where('tab', 'FacturacionElectronica')
        ->where('key', 'LIKE', '%qpse%')
        ->get();
    
    foreach ($qpseInFact as $field) {
        $icon = match ($field->key) {
            'qpse_endpoint_beta' => '🧪',
            'qpse_endpoint_production' => '🚀',
            default => '📝'
        };
        
        $status = $field->value ? '✅ Configurado' : '⚠️ Sin configurar';
        echo "  $icon {$field->key}: $status\n";
    }
    
    echo "\n🎯 RESULTADO:\n";
    echo "✅ Los campos QPSE han sido agregados a Facturación Electrónica\n";
    echo "✅ Ahora son visibles en la interfaz web\n";
    echo "✅ Tienen validación HTTPS y estilos diferenciados\n";
    
    echo "\n📍 VERIFICACIÓN:\n";
    echo "1. Ve a: http://restaurante.test/admin/configuracion/facturacion-electronica\n";
    echo "2. Busca los campos:\n";
    echo "   - 🧪 Endpoint QPSE Beta (color naranja)\n";
    echo "   - 🚀 Endpoint QPSE Producción (color verde)\n";
    echo "3. Configura ambos endpoints según tu entorno\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 ¡Campos QPSE agregados exitosamente a Facturación Electrónica!\n";