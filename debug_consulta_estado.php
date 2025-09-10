<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SunatService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

echo "🔍 DIAGNÓSTICO DE CONSULTA DE ESTADO SUNAT\n";
echo "═══════════════════════════════════════════\n\n";

// Verificar configuración
echo "📋 VERIFICANDO CONFIGURACIÓN:\n";
echo "- Modo Greenter: " . config('greenter.mode') . "\n";
echo "- RUC: " . config('greenter.company.ruc') . "\n";
echo "- Usuario SOL: " . config('greenter.company.clave_sol.user') . "\n";
echo "- Certificado: " . config('greenter.company.certificate') . "\n";
echo "- Existe certificado: " . (file_exists(config('greenter.company.certificate')) ? 'SÍ' : 'NO') . "\n\n";

// Solicitar ticket para probar
echo "🎫 Ingresa el ticket a consultar: ";
$ticket = trim(fgets(STDIN));

if (empty($ticket)) {
    echo "❌ Ticket vacío. Saliendo...\n";
    exit(1);
}

echo "\n🚀 Iniciando consulta de estado para ticket: {$ticket}\n\n";

try {
    $sunatService = new SunatService();
    
    echo "⏳ Consultando estado...\n";
    $startTime = microtime(true);
    
    $resultado = $sunatService->consultarEstadoResumen($ticket);
    
    $endTime = microtime(true);
    $processingTime = round(($endTime - $startTime) * 1000, 2);
    
    echo "\n📊 RESULTADO DE LA CONSULTA:\n";
    echo "═══════════════════════════\n";
    echo "- Éxito: " . ($resultado['success'] ? 'SÍ' : 'NO') . "\n";
    echo "- Tiempo: {$processingTime} ms\n";
    
    if ($resultado['success']) {
        echo "- Código: " . ($resultado['codigo'] ?? 'N/A') . "\n";
        echo "- Descripción: " . ($resultado['descripcion'] ?? 'N/A') . "\n";
        echo "- Estado: " . ($resultado['estado'] ?? 'N/A') . "\n";
        echo "- CDR disponible: " . (isset($resultado['cdr_content']) ? 'SÍ' : 'NO') . "\n";
        
        if (isset($resultado['cdr_path'])) {
            echo "- CDR guardado en: " . $resultado['cdr_path'] . "\n";
        }
    } else {
        echo "- Código de error: " . ($resultado['error_code'] ?? 'N/A') . "\n";
        echo "- Mensaje de error: " . ($resultado['error_message'] ?? 'N/A') . "\n";
        echo "- Mensaje usuario: " . ($resultado['message'] ?? 'N/A') . "\n";
    }
    
    echo "\n📄 RESPUESTA COMPLETA:\n";
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO:\n";
    echo "- Mensaje: " . $e->getMessage() . "\n";
    echo "- Archivo: " . $e->getFile() . "\n";
    echo "- Línea: " . $e->getLine() . "\n";
    echo "- Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ Diagnóstico completado.\n";