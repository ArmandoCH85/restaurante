<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Purchase;
use App\Models\IngredientStock;

echo "=== VERIFICACIÓN DE COMPRAS ===\n\n";

// Obtener todas las compras
$purchases = Purchase::all();
echo "Total de compras: " . $purchases->count() . "\n\n";

foreach ($purchases as $purchase) {
    echo "=== Compra ID: {$purchase->id} ===\n";
    echo "Estado: {$purchase->status}\n";
    echo "Fecha: {$purchase->purchase_date}\n";
    echo "Almacén ID: {$purchase->warehouse_id}\n";
    
    // Verificar si tiene detalles
    $details = $purchase->details;
    echo "Detalles: " . $details->count() . "\n";
    
    // Verificar si se crearon registros en ingredient_stock
    $stocks = IngredientStock::where('purchase_id', $purchase->id)->get();
    echo "Registros en ingredient_stock: " . $stocks->count() . "\n";
    
    if ($stocks->count() > 0) {
        foreach ($stocks as $stock) {
            echo "  - Ingrediente ID: {$stock->ingredient_id}, Cantidad: {$stock->quantity}, Costo: {$stock->unit_cost}\n";
        }
    }
    
    echo "\n";
}

echo "=== RESUMEN DE INGREDIENT_STOCK ===\n";
$totalStocks = IngredientStock::count();
echo "Total registros en ingredient_stock: {$totalStocks}\n";

$stocksWithPurchase = IngredientStock::whereNotNull('purchase_id')->count();
echo "Registros con purchase_id: {$stocksWithPurchase}\n";

$stocksWithoutPurchase = IngredientStock::whereNull('purchase_id')->count();
echo "Registros sin purchase_id: {$stocksWithoutPurchase}\n";