<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANÁLISIS INMEDIATO DE FACTURA 949 ===" . PHP_EOL;

$invoice = App\Models\Invoice::find(949);

if ($invoice) {
    echo "Factura 949:" . PHP_EOL;
    echo "- Serie: " . $invoice->series . PHP_EOL;
    echo "- Método en factura: " . ($invoice->payment_method ?? 'NULL') . PHP_EOL;
    echo "- Creada: " . $invoice->created_at . PHP_EOL;
    echo "- Order ID: " . ($invoice->order_id ?? 'NULL') . PHP_EOL;
    
    if ($invoice->order_id) {
        $order = $invoice->order;
        if ($order) {
            $order->load('payments');
            echo "- Pagos registrados: " . $order->payments->count() . PHP_EOL;
            foreach ($order->payments as $payment) {
                echo "  * " . $payment->payment_method . ": " . $payment->amount . " (creado: " . $payment->created_at . ")" . PHP_EOL;
            }
            
            // IDENTIFICAR EL FLUJO POR LA SERIE
            if (str_starts_with($invoice->series, 'NV001')) {
                echo "🎯 FLUJO: PosController" . PHP_EOL;
            } elseif (str_starts_with($invoice->series, 'NV002')) {
                echo "🎯 FLUJO: UnifiedPaymentController" . PHP_EOL;
            } else {
                echo "🎯 FLUJO: Filament u otro ({$invoice->series})" . PHP_EOL;
            }
        }
    }
} else {
    echo "❌ Factura 949 no encontrada" . PHP_EOL;
}

echo PHP_EOL . "Continuando con análisis completo..." . PHP_EOL;