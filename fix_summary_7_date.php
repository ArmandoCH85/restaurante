<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Summary;
use App\Models\Invoice;
use Carbon\Carbon;

echo "=== CORRIGIENDO FECHA DE REFERENCIA RESUMEN ID 7 ===\n";

// Obtener el resumen
$summary = Summary::find(7);
if (!$summary) {
    echo "No se encontró el resumen con ID 7\n";
    exit;
}

echo "Estado actual del resumen:\n";
echo "- ID: {$summary->id}\n";
echo "- Correlativo: {$summary->correlative}\n";
echo "- Fecha de Referencia: {$summary->fecha_referencia}\n";
echo "- Estado: {$summary->status}\n";
echo "\n";

// Actualizar la fecha de referencia a hoy
$today = Carbon::today()->format('Y-m-d');
echo "Actualizando fecha de referencia a: {$today}\n";

$summary->fecha_referencia = $today;
$summary->save();

echo "✓ Fecha de referencia actualizada\n";
echo "\n";

// Verificar las boletas que ahora debería incluir
echo "=== VERIFICANDO BOLETAS PARA LA NUEVA FECHA ===\n";

$invoices = Invoice::whereDate('issue_date', $today)
    ->where('invoice_type', 'receipt')
    ->where('sunat_status', 'ACEPTADO')
    ->get();

echo "Boletas ACEPTADAS del {$today}: " . $invoices->count() . "\n";

if ($invoices->count() > 0) {
    $totalAmount = $invoices->sum('total');
    echo "Monto total: S/ {$totalAmount}\n";
    echo "\nDetalles:\n";
    foreach ($invoices as $invoice) {
        echo "- {$invoice->series}-{$invoice->number}: S/ {$invoice->total}\n";
    }
    
    // Actualizar los contadores del resumen
    echo "\n=== ACTUALIZANDO CONTADORES DEL RESUMEN ===\n";
    $summary->receipts_count = $invoices->count();
    $summary->total_amount = $totalAmount;
    $summary->save();
    
    echo "✓ Contadores actualizados:\n";
    echo "- Cantidad de boletas: {$summary->receipts_count}\n";
    echo "- Monto total: S/ {$summary->total_amount}\n";
} else {
    echo "No hay boletas ACEPTADAS para incluir en el resumen\n";
    
    // Verificar boletas pendientes
    $pendingInvoices = Invoice::whereDate('issue_date', $today)
        ->where('invoice_type', 'receipt')
        ->where('sunat_status', 'PENDIENTE')
        ->get();
    
    if ($pendingInvoices->count() > 0) {
        echo "\nHay {$pendingInvoices->count()} boletas PENDIENTES que necesitan ser enviadas a SUNAT primero\n";
        echo "IDs de boletas pendientes: " . $pendingInvoices->pluck('id')->implode(', ') . "\n";
    }
}

echo "\n=== RESUMEN FINAL ===\n";
$summary->refresh();
echo "Resumen ID {$summary->id} actualizado:\n";
echo "- Fecha de Referencia: {$summary->fecha_referencia}\n";
echo "- Cantidad de Boletas: {$summary->receipts_count}\n";
echo "- Monto Total: S/ {$summary->total_amount}\n";
echo "- Estado: {$summary->status}\n";