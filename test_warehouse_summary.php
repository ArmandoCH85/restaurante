<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Warehouse;

// Crear la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACIÓN DEL RESUMEN POR ALMACÉN ===\n\n";

// Obtener todos los almacenes
$warehouses = Warehouse::all();

foreach ($warehouses as $warehouse) {
    echo "Almacén: {$warehouse->name} (ID: {$warehouse->id})\n";
    echo "Tipo: " . ($warehouse->is_default ? 'Principal' : 'Secundario') . "\n";
    
    // Obtener el stock de ingredientes en este almacén
    $ingredientStocks = IngredientStock::where('warehouse_id', $warehouse->id)
        ->where('status', 'available')
        ->with('ingredient')
        ->get();
    
    $totalValue = 0;
    
    if ($ingredientStocks->count() > 0) {
        echo "Ingredientes en stock:\n";
        foreach ($ingredientStocks as $stock) {
            $ingredient = $stock->ingredient;
            $value = $stock->quantity * $stock->unit_cost;
            $totalValue += $value;
            
            echo "  - {$ingredient->name} (Código: {$ingredient->code})\n";
            echo "    Cantidad: {$stock->quantity}, Costo unitario: S/ {$stock->unit_cost}, Valor total: S/ {$value}\n";
        }
    } else {
        echo "Sin stock de ingredientes\n";
    }
    
    echo "Subtotal Almacén: S/ " . number_format($totalValue, 2) . "\n";
    echo str_repeat("-", 50) . "\n\n";
}

// Calcular el total general
$totalGeneral = IngredientStock::where('status', 'available')
    ->selectRaw('SUM(quantity * unit_cost) as total')
    ->value('total') ?? 0;

echo "Total General de Inventario: S/ " . number_format($totalGeneral, 2) . "\n";

// Verificar ingredientes específicos
echo "\n=== VERIFICACIÓN DE INGREDIENTES ESPECÍFICOS ===\n";

$ingredientsToCheck = ['pollo', 'Arroz', 'ACEITE ALPA * 5 LT.'];

foreach ($ingredientsToCheck as $ingredientName) {
    $ingredient = Ingredient::where('name', 'LIKE', "%{$ingredientName}%")->first();
    
    if ($ingredient) {
        echo "\nIngrediente: {$ingredient->name} (ID: {$ingredient->id})\n";
        echo "Stock actual: {$ingredient->current_stock}\n";
        echo "Costo actual: S/ {$ingredient->current_cost}\n";
        
        $stocks = IngredientStock::where('ingredient_id', $ingredient->id)
            ->where('status', 'available')
            ->with('warehouse')
            ->get();
        
        if ($stocks->count() > 0) {
            echo "Distribución por almacén:\n";
            foreach ($stocks as $stock) {
                echo "  - {$stock->warehouse->name}: {$stock->quantity} unidades a S/ {$stock->unit_cost} c/u\n";
            }
        } else {
            echo "Sin registros de stock disponible\n";
        }
    } else {
        echo "\nIngrediente '{$ingredientName}' no encontrado\n";
    }
}