<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AppSetting;

echo "=== CONFIGURACIÓN ACTUAL DE QPS ===\n";
echo "===================================\n\n";

// Verificar entorno
$isProduction = AppSetting::getSetting('FacturacionElectronica', 'sunat_production') === '1';
echo "🌍 Entorno SUNAT: " . ($isProduction ? 'PRODUCCIÓN' : 'BETA/DEMO') . "\n";
echo "📍 Environment detectado: " . ($isProduction ? 'production' : 'beta') . "\n\n";

// Verificar endpoints
echo "📡 ENDPOINTS CONFIGURADOS:\n";
$endpointBeta = AppSetting::getSetting('FacturacionElectronica', 'qpse_endpoint_beta');
$endpointProd = AppSetting::getSetting('FacturacionElectronica', 'qpse_endpoint_production');

echo "🧪 Beta: " . ($endpointBeta ?: 'NO CONFIGURADO') . "\n";
echo "🚀 Producción: " . ($endpointProd ?: 'NO CONFIGURADO') . "\n\n";

// Verificar credenciales
echo "🔐 CREDENCIALES:\n";
$username = AppSetting::getSetting('FacturacionElectronica', 'qpse_username');
$password = AppSetting::getSetting('FacturacionElectronica', 'qpse_password');

echo "👤 Usuario: " . ($username ?: 'NO CONFIGURADO') . "\n";
echo "🔑 Contraseña: " . ($password ? 'CONFIGURADA' : 'NO CONFIGURADA') . "\n\n";

// Verificar qué endpoint se usaría
echo "🎯 ENDPOINT ACTIVO:\n";
$activeEndpoint = $isProduction ? $endpointProd : $endpointBeta;
echo "📍 URL activa: " . ($activeEndpoint ?: 'NO CONFIGURADO - USARÁ FALLBACK') . "\n";

if (!$activeEndpoint) {
    echo "⚠️ ADVERTENCIA: No hay endpoint configurado para el entorno actual\n";
    echo "📋 Se usará configuración estática: https://demo-cpe.qpse.pe\n";
}

echo "\n🔍 DIAGNÓSTICO DEL ERROR 409:\n";
echo "===============================\n";
if ($isProduction && !$endpointProd) {
    echo "❌ PROBLEMA ENCONTRADO:\n";
    echo "   - Entorno configurado: PRODUCCIÓN\n";
    echo "   - Endpoint producción: NO CONFIGURADO\n";
    echo "   - Resultado: Se usa fallback demo-cpe.qpse.pe\n";
    echo "   - Error: Credenciales de producción vs endpoint demo\n\n";
    
    echo "✅ SOLUCIÓN:\n";
    echo "   1. Configurar endpoint de producción: https://cpe.qpse.pe\n";
    echo "   2. O cambiar a entorno BETA temporalmente\n";
} elseif (!$isProduction && !$endpointBeta) {
    echo "❌ PROBLEMA ENCONTRADO:\n";
    echo "   - Entorno configurado: BETA\n";
    echo "   - Endpoint beta: NO CONFIGURADO\n";
    echo "   - Resultado: Se usa fallback demo-cpe.qpse.pe\n\n";
    
    echo "✅ SOLUCIÓN:\n";
    echo "   1. Configurar endpoint beta: https://demo-cpe.qpse.pe\n";
} else {
    echo "✅ Configuración parece correcta\n";
    echo "🔍 El error 409 puede deberse a:\n";
    echo "   - Credenciales incorrectas para el entorno\n";
    echo "   - Endpoint mal configurado\n";
    echo "   - Problema temporal del servicio\n";
}

echo "\n📋 PASOS PARA CORREGIR:\n";
echo "======================\n";
echo "1. Ve a: http://restaurante.test/admin/configuracion/facturacion-electronica\n";
echo "2. Configura los endpoints correctos:\n";
echo "   🧪 Beta: https://demo-cpe.qpse.pe\n";
echo "   🚀 Producción: https://cpe.qpse.pe\n";
echo "3. Verifica que las credenciales sean correctas para el entorno\n";
echo "4. Prueba nuevamente el envío\n";