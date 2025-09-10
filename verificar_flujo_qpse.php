<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SunatService;
use App\Services\QpsService;
use App\Models\Summary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "🔍 VERIFICACIÓN DE FLUJO QPSE PARA RESÚMENES DE BOLETAS\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "📋 PUNTOS DE VERIFICACIÓN PARA CONFIRMAR ENVÍO VÍA QPSE:\n\n";

// 1. Verificar configuración QPSE
echo "1️⃣ VERIFICANDO CONFIGURACIÓN QPSE...\n";
try {
    $qpsService = new QpsService();
    $config = $qpsService->getConfiguration();
    
    echo "   ✅ Servicio QPS configurado:\n";
    echo "   - Endpoint Beta: " . ($config['endpoint_beta'] ?? 'No configurado') . "\n";
    echo "   - Endpoint Producción: " . ($config['endpoint_produccion'] ?? 'No configurado') . "\n";
    echo "   - Usuario: " . ($config['usuario'] ? 'Configurado' : 'No configurado') . "\n";
    echo "   - Contraseña: " . ($config['password'] ? 'Configurada' : 'No configurada') . "\n";
    echo "   - Ambiente actual: " . ($config['environment'] ?? 'No detectado') . "\n\n";
    
    // Verificar disponibilidad del servicio
    $disponible = $qpsService->isServiceAvailable();
    echo "   🌐 Disponibilidad del servicio QPSE: " . ($disponible ? '✅ DISPONIBLE' : '❌ NO DISPONIBLE') . "\n\n";
    
} catch (Exception $e) {
    echo "   ❌ Error al verificar configuración: {$e->getMessage()}\n\n";
}

// 2. Verificar resúmenes enviados con tickets QPS
echo "2️⃣ VERIFICANDO RESÚMENES ENVIADOS VÍA QPS...\n";
try {
    $resumenes = Summary::whereNotNull('ticket')
        ->where('ticket', 'LIKE', 'TICKET_QPS_%')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    if ($resumenes->isEmpty()) {
        echo "   ⚠️ No se encontraron resúmenes con tickets QPS\n";
        echo "   💡 Esto puede significar que aún no se han enviado resúmenes vía QPSE\n\n";
    } else {
        echo "   ✅ Encontrados {$resumenes->count()} resúmenes con tickets QPS:\n\n";
        
        foreach ($resumenes as $resumen) {
            echo "   📄 Resumen ID: {$resumen->id}\n";
            echo "      - Correlativo: {$resumen->correlativo}\n";
            echo "      - Ticket: {$resumen->ticket}\n";
            echo "      - Estado: {$resumen->status}\n";
            echo "      - Fecha: {$resumen->created_at}\n";
            echo "      - 🎯 EVIDENCIA: Ticket con prefijo TICKET_QPS_ confirma envío vía QPSE\n\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Error al consultar resúmenes: {$e->getMessage()}\n\n";
}

// 3. Verificar método de envío en SunatService
echo "3️⃣ VERIFICANDO MÉTODO DE ENVÍO EN CÓDIGO...\n";
echo "   📝 Revisando SunatService::enviarResumenBoletas()...\n";

// Leer el archivo SunatService para verificar el uso de QPS
$sunatServicePath = __DIR__ . '/app/Services/SunatService.php';
if (file_exists($sunatServicePath)) {
    $contenido = file_get_contents($sunatServicePath);
    
    // Buscar evidencias de uso de QPS
    $evidencias = [
        'qpse.pe' => strpos($contenido, 'qpse.pe') !== false,
        'QpsService' => strpos($contenido, 'QpsService') !== false,
        'sendSignedXml' => strpos($contenido, 'sendSignedXml') !== false,
        'TICKET_QPS_' => strpos($contenido, 'TICKET_QPS_') !== false,
        'QPS (qpse.pe)' => strpos($contenido, 'QPS (qpse.pe)') !== false
    ];
    
    echo "   🔍 EVIDENCIAS EN EL CÓDIGO:\n";
    foreach ($evidencias as $evidencia => $encontrada) {
        $status = $encontrada ? '✅' : '❌';
        echo "      {$status} {$evidencia}: " . ($encontrada ? 'ENCONTRADO' : 'NO ENCONTRADO') . "\n";
    }
    
    $todasEncontradas = array_filter($evidencias);
    if (count($todasEncontradas) >= 3) {
        echo "\n   ✅ CONFIRMADO: El código está configurado para enviar vía QPSE\n\n";
    } else {
        echo "\n   ⚠️ ADVERTENCIA: Pocas evidencias de integración QPSE encontradas\n\n";
    }
} else {
    echo "   ❌ No se pudo leer el archivo SunatService.php\n\n";
}

// 4. Verificar logs de envío (si existen)
echo "4️⃣ VERIFICANDO LOGS DE ENVÍO...\n";
$logPaths = [
    'storage/logs/laravel.log',
    'storage/logs/qps-' . date('Y-m-d') . '.log',
    'storage/logs/qps-' . date('Y-m-d', strtotime('-1 day')) . '.log'
];

$logsEncontrados = false;
foreach ($logPaths as $logPath) {
    $fullPath = __DIR__ . '/' . $logPath;
    if (file_exists($fullPath)) {
        $logsEncontrados = true;
        echo "   📄 Revisando: {$logPath}\n";
        
        // Buscar líneas relacionadas con QPS/QPSE
        $contenidoLog = file_get_contents($fullPath);
        $lineasQPS = [];
        
        $lineas = explode("\n", $contenidoLog);
        foreach ($lineas as $linea) {
            if (stripos($linea, 'qps') !== false || 
                stripos($linea, 'qpse') !== false || 
                stripos($linea, 'resumen') !== false) {
                $lineasQPS[] = $linea;
            }
        }
        
        if (!empty($lineasQPS)) {
            echo "   ✅ Encontradas " . count($lineasQPS) . " líneas relacionadas con QPS/resúmenes\n";
            echo "   📝 Últimas 3 líneas relevantes:\n";
            foreach (array_slice($lineasQPS, -3) as $linea) {
                echo "      » " . trim($linea) . "\n";
            }
        } else {
            echo "   ⚠️ No se encontraron líneas relacionadas con QPS en este log\n";
        }
        echo "\n";
    }
}

if (!$logsEncontrados) {
    echo "   ⚠️ No se encontraron archivos de log\n";
    echo "   💡 Los logs se generan cuando se envían documentos\n\n";
}

// 5. Verificar configuración de base de datos
echo "5️⃣ VERIFICANDO CONFIGURACIÓN EN BASE DE DATOS...\n";
try {
    $settings = DB::table('settings')
        ->whereIn('key', [
            'qpse_endpoint_beta',
            'qpse_endpoint_produccion', 
            'qpse_usuario',
            'qpse_password',
            'sunat_production'
        ])
        ->pluck('value', 'key');
    
    if ($settings->isEmpty()) {
        echo "   ⚠️ No se encontraron configuraciones QPSE en la base de datos\n";
        echo "   💡 Configurar desde: Panel Admin → Configuración → Facturación Electrónica\n\n";
    } else {
        echo "   ✅ Configuraciones QPSE encontradas:\n";
        foreach ($settings as $key => $value) {
            $displayValue = in_array($key, ['qpse_password']) ? '***OCULTO***' : $value;
            echo "      - {$key}: {$displayValue}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error al consultar configuración: {$e->getMessage()}\n\n";
}

// Resumen final
echo "🎯 RESUMEN DE VERIFICACIÓN:\n";
echo "═══════════════════════════════════\n\n";

echo "📋 CÓMO CONFIRMAR QUE LOS RESÚMENES VAN VÍA QPSE:\n\n";

echo "✅ **EVIDENCIAS TÉCNICAS:**\n";
echo "   1. Tickets con formato 'TICKET_QPS_XXXXXXX' en la tabla summaries\n";
echo "   2. Código en SunatService.php que usa QpsService::sendSignedXml()\n";
echo "   3. Logs que mencionan 'QPS (qpse.pe)' como método de envío\n";
echo "   4. Configuración QPSE en settings de la base de datos\n\n";

echo "📊 **EVIDENCIAS FUNCIONALES:**\n";
echo "   1. Resúmenes con estado 'ACEPTADO' y tickets QPS\n";
echo "   2. Tiempo de respuesta rápido (típico de QPSE)\n";
echo "   3. No errores 'Not Found' al consultar estado\n";
echo "   4. Archivos XML almacenados en storage/app/sunat/summaries/\n\n";

echo "🔍 **VERIFICACIÓN EN TIEMPO REAL:**\n";
echo "   1. Enviar un resumen de prueba\n";
echo "   2. Verificar que el ticket generado tenga formato 'TICKET_QPS_'\n";
echo "   3. Revisar logs en storage/logs/ para confirmar envío vía QPSE\n";
echo "   4. Consultar estado del resumen (debe ser ACEPTADO automáticamente)\n\n";

echo "📱 **MONITOREO CONTINUO:**\n";
echo "   • Panel Admin → Resúmenes de Boletas\n";
echo "   • Comando: php artisan sunat:send-daily-summary\n";
echo "   • Logs: tail -f storage/logs/laravel.log | grep QPS\n";
echo "   • Base de datos: SELECT * FROM summaries WHERE ticket LIKE 'TICKET_QPS_%'\n\n";

echo "✅ CONCLUSIÓN:\n";
echo "Si encuentras tickets con formato 'TICKET_QPS_' en tus resúmenes,\n";
echo "CONFIRMA que están siendo enviados a SUNAT a través de QPSE.\n\n";

echo "🚀 Para generar evidencia fresca, ejecuta:\n";
echo "   php artisan sunat:send-daily-summary\n\n";

echo "✅ Verificación completada.\n";