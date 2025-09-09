<?php

require_once __DIR__ . '/vendor/autoload.php';

// Inicializar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Services\SunatService;
use Illuminate\Support\Facades\Log;

echo "=== PRUEBA DE NOTA DE CRÉDITO CON QPS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Buscar una factura aceptada para crear nota de crédito
    $invoice = Invoice::where('sunat_status', 'ACEPTADO')
        ->whereIn('invoice_type', ['receipt', 'invoice'])
        ->where('series', 'NOT LIKE', 'NV%')
        ->first();
    
    if (!$invoice) {
        echo "❌ No se encontró ninguna factura ACEPTADA para crear nota de crédito\n";
        echo "💡 Asegúrese de tener al menos una factura con estado ACEPTADO\n";
        exit(1);
    }
    
    echo "📋 Factura encontrada:\n";
    echo "   - ID: {$invoice->id}\n";
    echo "   - Serie-Número: {$invoice->series}-{$invoice->number}\n";
    echo "   - Tipo: {$invoice->invoice_type}\n";
    echo "   - Total: S/ {$invoice->total}\n";
    echo "   - Estado SUNAT: {$invoice->sunat_status}\n\n";
    
    // Crear instancia del servicio SUNAT
    $sunatService = new SunatService();
    
    echo "🚀 Creando nota de crédito con QPS...\n";
    echo "   - Motivo: 01 (Anulación de la operación)\n";
    echo "   - Método de envío: QPS (qpse.pe)\n\n";
    
    // Emitir nota de crédito
    $result = $sunatService->emitirNotaCredito(
        $invoice,
        '01',
        'ANULACION DE LA OPERACION'
    );
    
    if ($result['success']) {
        $creditNote = $result['credit_note'];
        echo "✅ Nota de crédito creada exitosamente:\n";
        echo "   - ID: {$creditNote->id}\n";
        echo "   - Serie-Número: {$creditNote->serie}-{$creditNote->numero}\n";
        echo "   - Total: S/ {$creditNote->total}\n";
        echo "   - Estado SUNAT: {$creditNote->sunat_status}\n";
        echo "   - Código SUNAT: {$creditNote->sunat_code}\n";
        echo "   - Descripción: {$creditNote->sunat_description}\n\n";
        
        if (isset($result['sunat_response']['hash_code'])) {
            echo "🔐 Hash Code: {$result['sunat_response']['hash_code']}\n";
        }
        
        echo "📁 Archivos generados:\n";
        if ($creditNote->xml_path) {
            echo "   - XML: {$creditNote->xml_path}\n";
        }
        if ($creditNote->cdr_path) {
            echo "   - CDR: {$creditNote->cdr_path}\n";
        }
        
    } else {
        echo "❌ Error al crear nota de crédito:\n";
        echo "   - Error: {$result['error']}\n";
    }
    
} catch (Exception $e) {
    echo "💥 Excepción capturada:\n";
    echo "   - Mensaje: {$e->getMessage()}\n";
    echo "   - Archivo: {$e->getFile()}\n";
    echo "   - Línea: {$e->getLine()}\n";
}

echo "\n📋 Revise el log detallado en: storage/logs/envionotacredito-" . date('Y-m-d') . ".log\n";
echo "🔍 Para ver logs QPS: storage/logs/qps-" . date('Y-m-d') . ".log\n";
echo "\n=== FIN DE LA PRUEBA ===\n";