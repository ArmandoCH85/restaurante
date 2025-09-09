<?php

/**
 * Script FORZADO para crear campos QPSE en Facturación Electrónica
 */

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AppSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

echo "🔥 CREACIÓN FORZADA DE CAMPOS QPSE EN FACTURACIÓN ELECTRÓNICA\n";
echo "=============================================================\n\n";

try {
    // Verificar conexión a la base de datos
    echo "🔌 Verificando conexión a la base de datos...\n";
    DB::connection()->getPdo();
    echo "✅ Conexión exitosa\n\n";
    
    // Mostrar campos actuales de FacturacionElectronica
    echo "📋 Campos actuales de FacturacionElectronica:\n";
    $currentFields = DB::table('app_settings')
        ->where('tab', 'FacturacionElectronica')
        ->get();
    
    foreach ($currentFields as $field) {
        echo "  - {$field->key}: " . ($field->value ?: '(vacío)') . "\n";
    }
    echo "Total: " . $currentFields->count() . " campos\n\n";
    
    // Eliminar campos QPSE existentes para recrearlos
    echo "🗑️ Eliminando campos QPSE existentes...\n";
    $deleted = DB::table('app_settings')
        ->where('tab', 'FacturacionElectronica')
        ->where('key', 'LIKE', '%qpse%')
        ->delete();
    echo "Eliminados: $deleted campos\n\n";
    
    // Crear campos QPSE desde cero
    echo "🔧 Creando campos QPSE desde cero...\n";
    $qpseFields = [
        [
            'id' => Str::uuid()->toString(),
            'tab' => 'FacturacionElectronica',
            'key' => 'qpse_endpoint_beta',
            'default' => '',
            'value' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => Str::uuid()->toString(),
            'tab' => 'FacturacionElectronica',
            'key' => 'qpse_endpoint_production',
            'default' => '',
            'value' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ];
    
    foreach ($qpseFields as $field) {
        DB::table('app_settings')->insert($field);
        $label = match ($field['key']) {
            'qpse_endpoint_beta' => '🧪 Endpoint QPSE Beta',
            'qpse_endpoint_production' => '🚀 Endpoint QPSE Producción',
            default => $field['key']
        };
        echo "✅ CREADO: $label\n";
        echo "   ID: {$field['id']}\n";
        echo "   Key: {$field['key']}\n";
    }
    
    echo "\n📋 Verificación final:\n";
    $finalFields = DB::table('app_settings')
        ->where('tab', 'FacturacionElectronica')
        ->where('key', 'LIKE', '%qpse%')
        ->get();
    
    foreach ($finalFields as $field) {
        $icon = match ($field->key) {
            'qpse_endpoint_beta' => '🧪',
            'qpse_endpoint_production' => '🚀',
            default => '📝'
        };
        echo "  $icon {$field->key}: CREADO\n";
    }
    
    echo "\n🎯 RESULTADO:\n";
    echo "✅ " . $finalFields->count() . " campos QPSE creados en FacturacionElectronica\n";
    echo "✅ Los campos están en la base de datos\n";
    echo "✅ Deberían aparecer en la interfaz\n";
    
    echo "\n📍 VERIFICACIÓN INMEDIATA:\n";
    echo "1. Ve a: http://restaurante.test/admin/configuracion/facturacion-electronica\n";
    echo "2. Recarga la página completamente (Ctrl+Shift+R)\n";
    echo "3. Busca en la tabla:\n";
    echo "   - 🧪 Endpoint QPSE Beta\n";
    echo "   - 🚀 Endpoint QPSE Producción\n";
    echo "4. Si aún no aparecen, el problema está en el Resource\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🔥 ¡CREACIÓN FORZADA COMPLETADA!\n";