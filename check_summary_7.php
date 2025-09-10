<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Summary;
use App\Models\Invoice;
use Carbon\Carbon;

echo "=== VERIFICACIÓN RESUMEN ID 7 ===\n";

// Obtener el resumen
$summary = Summary::find(7);
if (!$summary) {
    echo "No se encontró el resumen con ID 7\n";
    exit;
}

echo "Resumen ID: {$summary->id}\n";
echo "Correlativo: {$summary->correlative}\n";
echo "Fecha de Referencia: {$summary->reference_date}\n";
echo "Estado SUNAT: {$summary->sunat_status}\n";
echo "\n";

// Buscar boletas para la fecha de referencia
$referenceDate = $summary->reference_date;
echo "Buscando boletas para fecha: {$referenceDate}\n";

// Todas las boletas de la fecha
$allInvoices = Invoice::whereDate('issue_date', $referenceDate)
    ->where('invoice_type', 'receipt')
    ->get();

echo "Total boletas del {$referenceDate}: " . $allInvoices->count() . "\n";

// Boletas por estado SUNAT
$aceptadas = $allInvoices->where('sunat_status', 'ACEPTADO')->count();
$pendientes = $allInvoices->where('sunat_status', 'PENDIENTE')->count();
$rechazadas = $allInvoices->where('sunat_status', 'RECHAZADO')->count();

echo "- ACEPTADAS: {$aceptadas}\n";
echo "- PENDIENTES: {$pendientes}\n";
echo "- RECHAZADAS: {$rechazadas}\n";
echo "\n";

// Verificar fecha de hoy
$today = Carbon::today()->format('Y-m-d');
echo "Fecha de hoy: {$today}\n";

if ($referenceDate === $today) {
    echo "✓ La fecha de referencia coincide con hoy\n";
} else {
    echo "✗ La fecha de referencia NO coincide con hoy\n";
    echo "Diferencia: " . Carbon::parse($referenceDate)->diffInDays(Carbon::today()) . " días\n";
}

echo "\n=== BOLETAS DE HOY ===\n";
$todayInvoices = Invoice::whereDate('issue_date', $today)
    ->where('invoice_type', 'receipt')
    ->get();

echo "Total boletas de hoy ({$today}): " . $todayInvoices->count() . "\n";

if ($todayInvoices->count() > 0) {
    echo "\nDetalles de boletas de hoy:\n";
    foreach ($todayInvoices as $invoice) {
        echo "- ID: {$invoice->id}, Serie: {$invoice->series}, Número: {$invoice->number}, Estado: {$invoice->sunat_status}, Total: {$invoice->total}\n";
    }
}

echo "\n=== ANÁLISIS ===\n";
if ($referenceDate !== $today) {
    echo "PROBLEMA: El resumen tiene fecha de referencia {$referenceDate} pero hoy es {$today}\n";
    echo "SOLUCIÓN: Actualizar la fecha de referencia del resumen a la fecha actual\n";
} else {
    echo "La fecha de referencia es correcta\n";
    if ($aceptadas === 0 && $pendientes > 0) {
        echo "PROBLEMA: Hay boletas pendientes que necesitan ser enviadas a SUNAT\n";
    }
}