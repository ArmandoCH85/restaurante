<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Obtener la factura
$invoice = App\Models\Invoice::find(165);
$invoice->load('details.product');

echo "🔍 ANÁLISIS DE FACTURA #165\n";
echo "==========================\n\n";

echo "📄 DATOS DE LA FACTURA:\n";
echo "Número: {$invoice->series}-{$invoice->number}\n";
echo "Tipo: {$invoice->invoice_type}\n";
echo "Fecha: {$invoice->issue_date}\n\n";

echo "💰 TOTALES EN BD:\n";
echo "Subtotal: S/ " . number_format($invoice->taxable_amount, 2) . "\n";
echo "IGV: S/ " . number_format($invoice->tax, 2) . "\n";
echo "Total: S/ " . number_format($invoice->total, 2) . "\n\n";

echo "📝 DETALLES DE PRODUCTOS:\n";
foreach($invoice->details as $detail) {
    echo "- Producto: {$detail->product->name}\n";
    echo "  Precio en catálogo: S/ " . number_format($detail->product->price, 2) . "\n";
    echo "  Precio unitario (detalle): S/ " . number_format($detail->unit_price, 2) . "\n";
    echo "  Cantidad: {$detail->quantity}\n";
    echo "  Subtotal: S/ " . number_format($detail->subtotal, 2) . "\n\n";
}

echo "🧮 CÁLCULO CORRECTO (IGV INCLUIDO):\n";
$totalConIgv = $invoice->total; // S/ 29.50
$subtotalSinIgv = round($totalConIgv / 1.18, 2);
$igvIncluido = round($totalConIgv / 1.18 * 0.18, 2);

echo "Total con IGV: S/ " . number_format($totalConIgv, 2) . "\n";
echo "Subtotal sin IGV: S/ " . number_format($subtotalSinIgv, 2) . "\n";
echo "IGV incluido: S/ " . number_format($igvIncluido, 2) . "\n";
echo "Validación: S/ " . number_format($subtotalSinIgv + $igvIncluido, 2) . "\n\n";

echo "❓ PREGUNTA CLAVE:\n";
echo "¿El precio del producto (S/ " . number_format($detail->product->price, 2) . ") YA INCLUYE IGV?\n";
echo "Si SÍ: El total debería ser S/ " . number_format($detail->product->price, 2) . "\n";
echo "Si NO: El total debería ser S/ " . number_format($detail->product->price * 1.18, 2) . "\n";
