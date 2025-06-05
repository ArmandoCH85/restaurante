<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Obtener la factura
$invoice = App\Models\Invoice::find(165);
$invoice->load('details');

echo "🔧 CORRECCIÓN DE FACTURA #165\n";
echo "=============================\n\n";

echo "📄 DATOS ACTUALES:\n";
echo "Subtotal: S/ " . number_format($invoice->taxable_amount, 2) . "\n";
echo "IGV: S/ " . number_format($invoice->tax, 2) . "\n";
echo "Total: S/ " . number_format($invoice->total, 2) . "\n\n";

echo "📝 DETALLES ACTUALES:\n";
foreach($invoice->details as $detail) {
    echo "- {$detail->description}: {$detail->quantity} x S/ " . number_format($detail->unit_price, 2) . " = S/ " . number_format($detail->subtotal, 2) . "\n";
}

// El precio S/ 25.00 YA INCLUYE IGV
// Necesitamos corregir los cálculos
$totalConIgv = 25.00; // Este es el precio correcto que incluye IGV
$subtotalSinIgv = round($totalConIgv / 1.18, 2); // S/ 21.19
$igvIncluido = round($totalConIgv / 1.18 * 0.18, 2); // S/ 3.81

echo "\n🧮 CÁLCULO CORRECTO:\n";
echo "Total con IGV (precio del producto): S/ " . number_format($totalConIgv, 2) . "\n";
echo "Subtotal sin IGV: S/ " . number_format($subtotalSinIgv, 2) . "\n";
echo "IGV incluido: S/ " . number_format($igvIncluido, 2) . "\n";
echo "Validación: S/ " . number_format($subtotalSinIgv + $igvIncluido, 2) . "\n\n";

// Actualizar la factura
echo "💾 ACTUALIZANDO FACTURA...\n";

// Actualizar los detalles de la factura
foreach($invoice->details as $detail) {
    $detail->unit_price = $subtotalSinIgv; // Precio sin IGV
    $detail->subtotal = $subtotalSinIgv * $detail->quantity; // Subtotal sin IGV
    $detail->save();
    echo "- Detalle actualizado: {$detail->description} - Precio unitario: S/ " . number_format($detail->unit_price, 2) . "\n";
}

// Actualizar los totales de la factura
$invoice->taxable_amount = $subtotalSinIgv; // Subtotal sin IGV
$invoice->tax = $igvIncluido; // IGV incluido
$invoice->total = $totalConIgv; // Total con IGV
$invoice->save();

echo "\n✅ FACTURA CORREGIDA:\n";
echo "Nuevo subtotal: S/ " . number_format($invoice->taxable_amount, 2) . "\n";
echo "Nuevo IGV: S/ " . number_format($invoice->tax, 2) . "\n";
echo "Nuevo total: S/ " . number_format($invoice->total, 2) . "\n\n";

echo "🎯 RESULTADO FINAL:\n";
echo "La factura ahora muestra correctamente:\n";
echo "- Subtotal: S/ 21.19 (sin IGV)\n";
echo "- IGV (18%): S/ 3.81 (incluido en el precio)\n";
echo "- Total: S/ 25.00 (precio original del producto)\n";
