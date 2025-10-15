<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Ingredient;

echo "=== VERIFICACIÓN DE RELACIÓN PRODUCTOS-INGREDIENTES ===\n\n";

// Obtener todos los productos que son ingredientes
$ingredientProducts = Product::where('product_type', 'ingredient')
    ->orWhere('product_type', 'both')
    ->get();

echo "Productos marcados como ingredientes: " . $ingredientProducts->count() . "\n\n";

foreach ($ingredientProducts as $product) {
    echo "Producto ID: {$product->id}\n";
    echo "Nombre: {$product->name}\n";
    echo "Código: {$product->code}\n";
    
    // Buscar el ingrediente correspondiente
    $ingredient = Ingredient::where('code', $product->code)->first();
    
    if ($ingredient) {
        echo "✓ Ingrediente encontrado - ID: {$ingredient->id}\n";
    } else {
        echo "✗ NO se encontró ingrediente correspondiente\n";
        echo "  Se necesita crear ingrediente con código: {$product->code}\n";
    }
    echo "---\n";
}

// Verificar ingredientes sin producto correspondiente
echo "\n=== INGREDIENTES SIN PRODUCTO CORRESPONDIENTE ===\n";
$ingredients = Ingredient::all();
$orphanIngredients = [];

foreach ($ingredients as $ingredient) {
    $product = Product::where('code', $ingredient->code)
        ->where(function($query) {
            $query->where('product_type', 'ingredient')
                  ->orWhere('product_type', 'both');
        })
        ->first();
    if (!$product) {
        $orphanIngredients[] = $ingredient;
    }
}

echo "Ingredientes sin producto correspondiente: " . count($orphanIngredients) . "\n";
foreach ($orphanIngredients as $ingredient) {
    echo "- Ingrediente ID: {$ingredient->id}, Código: {$ingredient->code}, Nombre: {$ingredient->name}\n";
}

echo "\n=== RESUMEN ===\n";
echo "Total productos ingredientes: " . $ingredientProducts->count() . "\n";
echo "Total ingredientes: " . $ingredients->count() . "\n";
echo "Productos sin ingrediente: " . $ingredientProducts->filter(function($p) {
    return !Ingredient::where('code', $p->code)->exists();
})->count() . "\n";
echo "Ingredientes huérfanos: " . count($orphanIngredients) . "\n";