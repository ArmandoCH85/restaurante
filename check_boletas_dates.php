<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;

echo "Verificando fechas con boletas aceptadas...\n";

$fechas = Invoice::where('invoice_type', 'receipt')
    ->where('sunat_status', 'ACEPTADO')
    ->distinct()
    ->pluck('issue_date')
    ->sort();

if ($fechas->count() > 0) {
    echo "Fechas con boletas: \n";
    foreach ($fechas as $fecha) {
        $cantidad = Invoice::where('invoice_type', 'receipt')
            ->where('sunat_status', 'ACEPTADO')
            ->where('issue_date', $fecha)
            ->count();
        echo "- {$fecha}: {$cantidad} boletas\n";
    }
} else {
    echo "No se encontraron boletas aceptadas.\n";
}

echo "\nVerificando todas las boletas...\n";
$todasBoletas = Invoice::where('invoice_type', 'receipt')->count();
echo "Total de boletas: {$todasBoletas}\n";

$boletasAceptadas = Invoice::where('invoice_type', 'receipt')
    ->where('sunat_status', 'ACEPTADO')
    ->count();
echo "Boletas aceptadas: {$boletasAceptadas}\n";

$estados = Invoice::where('invoice_type', 'receipt')
    ->distinct()
    ->pluck('sunat_status');
echo "Estados encontrados: " . $estados->implode(', ') . "\n";