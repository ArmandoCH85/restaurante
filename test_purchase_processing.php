<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Purchase;
use App\Models\IngredientStock;

echo "=== PRUEBA DE PROCESAMIENTO DE COMPRA ===\n\n";

// Obtener la primera compra completada
$purchase = Purchase::where('status', 'completed')->first();

if (!$purchase) {
    echo "No se encontraron compras completadas.\n";
    exit;
}

echo "Procesando compra ID: {$purchase->id}\n";
echo "Estado actual: {$purchase->status}\n";
echo "Almacén: {$purchase->warehouse_id}\n";

// Verificar detalles de la compra
$details = $purchase->details;
echo "Detalles de la compra: " . $details->count() . "\n";

foreach ($details as $detail) {
    echo "  - Producto ID: {$detail->product_id}, Cantidad: {$detail->quantity}, Costo: {$detail->unit_cost}\n";
    
    $product = $detail->product;
    if ($product) {
        echo "    Producto: {$product->name}\n";
        echo "    Es ingrediente: " . ($product->isIngredient() ? 'Sí' : 'No') . "\n";
    }
}

echo "\nRegistros actuales en ingredient_stock para esta compra: " . 
     IngredientStock::where('purchase_id', $purchase->id)->count() . "\n";

echo "\nEjecutando processOrder()...\n";

// Ejecutar el procesamiento
$result = $purchase->processOrder();

echo "Resultado del procesamiento:\n";
print_r($result);

echo "\nRegistros en ingredient_stock después del procesamiento: " . 
     IngredientStock::where('purchase_id', $purchase->id)->count() . "\n";

$stocks = IngredientStock::where('purchase_id', $purchase->id)->get();
foreach ($stocks as $stock) {
    echo "  - Ingrediente ID: {$stock->ingredient_id}, Cantidad: {$stock->quantity}, Costo: {$stock->unit_cost}\n";
}