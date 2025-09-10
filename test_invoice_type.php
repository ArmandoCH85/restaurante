<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;

echo "🔍 VERIFICANDO TIPOS DE COMPROBANTES RECIENTES\n";
echo "================================================\n\n";

// Obtener las últimas 10 facturas creadas
$recentInvoices = Invoice::orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'series', 'number', 'invoice_type', 'issue_date', 'created_at', 'total']);

echo "📋 ÚLTIMAS 10 FACTURAS CREADAS:\n";
echo "ID\tSerie\tNúmero\tTipo\t\tFecha Emisión\tTotal\n";
echo "--\t-----\t------\t----\t\t-------------\t-----\n";

foreach ($recentInvoices as $invoice) {
    $typeDisplay = match($invoice->invoice_type) {
        'receipt' => '📄 BOLETA',
        'sales_note' => '📝 NOTA VENTA',
        'invoice' => '🧾 FACTURA',
        default => $invoice->invoice_type
    };
    
    echo sprintf(
        "%d\t%s\t%s\t%s\t%s\tS/ %.2f\n",
        $invoice->id,
        $invoice->series,
        $invoice->number,
        $typeDisplay,
        $invoice->issue_date->format('Y-m-d'),
        $invoice->total
    );
}

echo "\n📊 RESUMEN POR TIPO:\n";
echo "==================\n";

$typeCounts = Invoice::selectRaw('invoice_type, COUNT(*) as count')
    ->whereDate('created_at', '>=', Carbon::today()->subDays(7))
    ->groupBy('invoice_type')
    ->get();

foreach ($typeCounts as $typeCount) {
    $typeDisplay = match($typeCount->invoice_type) {
        'receipt' => '📄 BOLETAS',
        'sales_note' => '📝 NOTAS DE VENTA',
        'invoice' => '🧾 FACTURAS',
        default => strtoupper($typeCount->invoice_type)
    };
    
    echo sprintf("%s: %d\n", $typeDisplay, $typeCount->count);
}

echo "\n✅ Verificación completada.\n";