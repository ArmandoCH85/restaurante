<?php

/**
 * Script para verificar campos QPSE en Facturación Electrónica
 */

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AppSetting;
use Illuminate\Support\Str;

echo "🔍 VERIFICANDO CAMPOS QPSE EN FACTURACIÓN ELECTRÓNICA...\n";
echo "========================================================\n\n";

try {
    // Verificar todos los campos de Facturación Electrónica
    echo "📋 Todos los campos de Facturación Electrónica:\n";
    $factSettings = AppSetting::where('tab', 'FacturacionElectronica')->get();
    
    if ($factSettings->count() > 0) {
        foreach ($factSettings as $setting) {
            echo "  - {$setting->key}: " . ($setting->value ?: '(vacío)') . "\n";
        }
    } else {
        echo "  ❌ No hay campos en FacturacionElectronica\n";
    }
    
    echo "\nTotal: " . $factSettings->count() . " campos\n\n";
    
    // Verificar específicamente los campos QPSE
    echo "🎯 Verificando campos QPSE específicos:\n";
    $qpseFields = ['qpse_endpoint_beta', 'qpse_endpoint_production'];
    
    foreach ($qpseFields as $field) {
        $setting = AppSetting::where('tab', 'FacturacionElectronica')
            ->where('key', $field)
            ->first();
        
        if ($setting) {
            echo "  ✅ $field: EXISTE\n";
            echo "     ID: {$setting->id}\n";
            echo "     Value: " . ($setting->value ?: '(vacío)') . "\n";
            echo "     Created: {$setting->created_at}\n";
        } else {
            echo "  ❌ $field: NO EXISTE\n";
        }
        echo "\n";
    }
    
    // Crear campos si no existen
    echo "🔧 Creando campos faltantes...\n";
    $fieldsToCreate = [
        'qpse_endpoint_beta' => '🧪 Endpoint QPSE Beta',
        'qpse_endpoint_production' => '🚀 Endpoint QPSE Producción'
    ];
    
    $created = 0;
    foreach ($fieldsToCreate as $key => $label) {
        $exists = AppSetting::where('tab', 'FacturacionElectronica')
            ->where('key', $key)
            ->exists();
        
        if (!$exists) {
            $setting = AppSetting::create([
                'id' => Str::uuid()->toString(),
                'tab' => 'FacturacionElectronica',
                'key' => $key,
                'default' => '',
                'value' => '',
            ]);
            
            echo "✅ CREADO: $label\n";
            echo "   ID: {$setting->id}\n";
            $created++;
        } else {
            echo "ℹ️ YA EXISTE: $label\n";
        }
    }
    
    if ($created > 0) {
        echo "\n🎉 Se crearon $created campos nuevos.\n";
    } else {
        echo "\n✅ Todos los campos ya existen.\n";
    }
    
    // Verificación final
    echo "\n📋 Verificación final de campos QPSE:\n";
    $finalCheck = AppSetting::where('tab', 'FacturacionElectronica')
        ->where('key', 'LIKE', '%qpse%')
        ->get();
    
    foreach ($finalCheck as $field) {
        $icon = match ($field->key) {
            'qpse_endpoint_beta' => '🧪',
            'qpse_endpoint_production' => '🚀',
            default => '📝'
        };
        
        echo "  $icon {$field->key}: " . ($field->value ?: '(sin configurar)') . "\n";
    }
    
    echo "\n🎯 INSTRUCCIONES:\n";
    echo "1. Ve a: http://restaurante.test/admin/configuracion/facturacion-electronica\n";
    echo "2. Recarga la página (F5)\n";
    echo "3. Busca los campos 🧪 y 🚀 en la tabla\n";
    echo "4. Si no aparecen, hay un problema con el Resource\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Verificación completada.\n";