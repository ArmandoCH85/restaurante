<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Services\SunatService;
use App\Services\QpsService;
use App\Models\AppSetting;
use App\Models\DocumentSeries;

echo "\n=== DIAGNÓSTICO DE NOTAS DE CRÉDITO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Verificar configuración QPSE
echo "📋 1. VERIFICANDO CONFIGURACIÓN QPSE...\n";
try {
    // Verificar entorno
    $isProduction = \App\Models\AppSetting::getSetting('FacturacionElectronica', 'sunat_production') === '1';
    $environment = $isProduction ? 'production' : 'beta';
    
    // Obtener endpoint
    $endpoint = \App\Models\AppSetting::getQpseEndpointByEnvironmentFromFacturacion($environment);
    
    // Obtener credenciales
    $credentials = \App\Models\AppSetting::getQpseCredentialsFromFacturacion();
    
    echo "   ✅ Configuración QPSE encontrada:\n";
    echo "   - Entorno: {$environment}\n";
    echo "   - Endpoint: " . ($endpoint ?: 'N/A') . "\n";
    echo "   - Usuario: " . ($credentials['username'] ? '✓ Configurado' : '✗ No configurado') . "\n";
    echo "   - Contraseña: " . ($credentials['password'] ? '✓ Configurada' : '✗ No configurada') . "\n";
    
    if (empty($credentials['username'])) {
        throw new Exception('Usuario QPSE no configurado en FacturacionElectronica');
    }
    
    if (empty($credentials['password'])) {
        throw new Exception('Contraseña QPSE no configurada en FacturacionElectronica');
    }
    
    if (empty($endpoint)) {
        throw new Exception('Endpoint QPSE no configurado para entorno ' . $environment);
    }
    
    echo "   ✅ Configuración QPSE completa\n";
    
    // Probar conectividad si es posible
    try {
        $qpsService = new QpsService();
        if ($qpsService->isServiceAvailable()) {
            echo "   ✅ Servicio QPSE disponible\n";
            
            // Probar autenticación
            $token = $qpsService->getAccessToken();
            if ($token) {
                echo "   ✅ Autenticación QPSE exitosa\n";
            } else {
                echo "   ❌ Error en autenticación QPSE\n";
            }
        } else {
            echo "   ❌ Servicio QPSE no disponible\n";
        }
    } catch (Exception $serviceException) {
        echo "   ⚠️  No se pudo probar conectividad: " . $serviceException->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error en configuración QPSE: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Verificar series de notas de crédito
echo "📋 2. VERIFICANDO SERIES DE NOTAS DE CRÉDITO...\n";
try {
    $series = DocumentSeries::where('document_type', 'credit_note')
        ->where('active', true)
        ->first();
    
    if ($series) {
        echo "   ✅ Serie activa encontrada:\n";
        echo "   - Serie: {$series->series}\n";
        echo "   - Próximo número: {$series->current_number}\n";
        echo "   - Prefijo: {$series->prefix}\n";
        echo "   - Sufijo: {$series->suffix}\n";
    } else {
        echo "   ❌ No hay series activas para notas de crédito\n";
        echo "   💡 Solución: Configure una serie desde el panel de administración\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error al verificar series: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Verificar configuración de empresa
echo "📋 3. VERIFICANDO CONFIGURACIÓN DE EMPRESA...\n";
try {
    $ruc = AppSetting::getSetting('Empresa', 'ruc');
    $razonSocial = AppSetting::getSetting('Empresa', 'razon_social');
    $direccion = AppSetting::getSetting('Empresa', 'direccion');
    
    echo "   - RUC: " . ($ruc ?: 'NO CONFIGURADO') . "\n";
    echo "   - Razón Social: " . ($razonSocial ?: 'NO CONFIGURADA') . "\n";
    echo "   - Dirección: " . ($direccion ?: 'NO CONFIGURADA') . "\n";
    
    if ($ruc && $razonSocial && $direccion) {
        echo "   ✅ Configuración de empresa completa\n";
    } else {
        echo "   ❌ Configuración de empresa incompleta\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error al verificar empresa: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Buscar facturas para probar
echo "📋 4. BUSCANDO FACTURAS PARA PRUEBA...\n";
try {
    $invoices = Invoice::where('sunat_status', 'ACEPTADO')
        ->whereDoesntHave('creditNotes')
        ->orderBy('id', 'desc')
        ->limit(3)
        ->get();
    
    if ($invoices->count() > 0) {
        echo "   ✅ Facturas disponibles para prueba:\n";
        foreach ($invoices as $invoice) {
            echo "   - ID: {$invoice->id}, Serie: {$invoice->series}-{$invoice->number}, Total: S/ {$invoice->total}\n";
        }
        
        // Seleccionar la primera factura para prueba
        $testInvoice = $invoices->first();
        echo "\n   🧪 Probando con factura ID: {$testInvoice->id}\n";
        
    } else {
        echo "   ❌ No hay facturas aceptadas disponibles para prueba\n";
        $testInvoice = null;
    }
    
} catch (Exception $e) {
    echo "   ❌ Error al buscar facturas: " . $e->getMessage() . "\n";
    $testInvoice = null;
}

echo "\n";

// 5. Probar creación de nota de crédito
if ($testInvoice) {
    echo "📋 5. PROBANDO CREACIÓN DE NOTA DE CRÉDITO...\n";
    try {
        $sunatService = new SunatService();
        
        echo "   🔄 Iniciando proceso de emisión...\n";
        $result = $sunatService->emitirNotaCredito(
            $testInvoice,
            '01',
            'PRUEBA DE DIAGNÓSTICO - ANULACION DE LA OPERACION'
        );
        
        if ($result['success']) {
            $creditNote = $result['credit_note'];
            echo "   ✅ Nota de crédito creada exitosamente:\n";
            echo "   - ID: {$creditNote->id}\n";
            echo "   - Serie-Número: {$creditNote->series}-{$creditNote->number}\n";
            echo "   - Total: S/ {$creditNote->total}\n";
            echo "   - Estado SUNAT: {$creditNote->sunat_status}\n";
            echo "   - Código SUNAT: {$creditNote->sunat_code}\n";
            echo "   - Descripción: {$creditNote->sunat_description}\n";
            
            if ($creditNote->xml_path) {
                echo "   - XML: {$creditNote->xml_path}\n";
            }
            if ($creditNote->cdr_path) {
                echo "   - CDR: {$creditNote->cdr_path}\n";
            }
            
            // Verificar archivos
            if ($creditNote->xml_path && file_exists(storage_path('app/' . $creditNote->xml_path))) {
                echo "   ✅ Archivo XML generado correctamente\n";
            } else {
                echo "   ❌ Archivo XML no encontrado\n";
            }
            
            if ($creditNote->cdr_path && file_exists(storage_path('app/' . $creditNote->cdr_path))) {
                echo "   ✅ Archivo CDR generado correctamente\n";
            } else {
                echo "   ⚠️  Archivo CDR no encontrado (normal si hay errores)\n";
            }
            
        } else {
            echo "   ❌ Error al crear nota de crédito:\n";
            echo "   - Error: {$result['error']}\n";
        }
        
    } catch (Exception $e) {
        echo "   💥 Excepción capturada:\n";
        echo "   - Mensaje: {$e->getMessage()}\n";
        echo "   - Archivo: {$e->getFile()}\n";
        echo "   - Línea: {$e->getLine()}\n";
    }
} else {
    echo "📋 5. PRUEBA DE CREACIÓN OMITIDA (no hay facturas disponibles)\n";
}

echo "\n";

// 6. Verificar logs
echo "📋 6. VERIFICANDO LOGS...\n";
$logFiles = [
    'envionotacredito-' . date('Y-m-d') . '.log',
    'qps-' . date('Y-m-d') . '.log',
    'laravel-' . date('Y-m-d') . '.log'
];

foreach ($logFiles as $logFile) {
    $logPath = storage_path('logs/' . $logFile);
    if (file_exists($logPath)) {
        $size = filesize($logPath);
        echo "   ✅ {$logFile}: " . number_format($size) . " bytes\n";
        
        // Mostrar últimas líneas si hay errores
        $content = file_get_contents($logPath);
        if (strpos($content, 'ERROR') !== false || strpos($content, 'REJECTED') !== false) {
            echo "   ⚠️  Contiene errores - revisar manualmente\n";
        }
    } else {
        echo "   ❌ {$logFile}: No encontrado\n";
    }
}

echo "\n";

// 7. Recomendaciones
echo "📋 7. RECOMENDACIONES Y PRÓXIMOS PASOS...\n";
echo "\n🔧 Para solucionar problemas comunes:\n";
echo "   1. Verificar configuración QPSE en el panel de administración\n";
echo "   2. Asegurar que hay series activas para notas de crédito\n";
echo "   3. Revisar logs detallados en storage/logs/\n";
echo "   4. Probar conectividad con: php artisan qpse:test-config\n";
echo "   5. Verificar certificados digitales\n";

echo "\n📚 Archivos de logs para revisar:\n";
foreach ($logFiles as $logFile) {
    echo "   - storage/logs/{$logFile}\n";
}

echo "\n🌐 URLs importantes:\n";
echo "   - Panel de notas de crédito: http://restaurante.test/admin/facturacion/notas-credito\n";
echo "   - Documentación QPSE: https://docs.qpse.pe/\n";
echo "   - Configuración: http://restaurante.test/admin/configuracion\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
echo "Revise los resultados y siga las recomendaciones para solucionar problemas.\n\n";