<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRUEBA DE CORRECCIÓN SUNAT ===\n";
echo "Probando envío con nombre de archivo corregido...\n\n";

try {
    $invoice = \App\Models\Invoice::find(968);
    
    if (!$invoice) {
        echo "ERROR: No se encontró la factura con ID 968\n";
        exit(1);
    }
    
    echo "Factura encontrada: {$invoice->series}-{$invoice->number}\n";
    echo "Estado actual: {$invoice->sunat_status}\n";
    echo "Iniciando envío a SUNAT...\n\n";
    
    $service = new \App\Services\SunatService();
    $result = $service->emitirFactura(968);
    
    echo "\n=== RESULTADO ===\n";
    echo "Envío: " . ($result ? 'EXITOSO' : 'FALLIDO') . "\n";
    
    // Recargar factura para ver estado actualizado
    $invoice->refresh();
    echo "Estado final: {$invoice->sunat_status}\n";
    echo "Código SUNAT: {$invoice->sunat_code}\n";
    echo "Descripción: {$invoice->sunat_description}\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DE PRUEBA ===\n";