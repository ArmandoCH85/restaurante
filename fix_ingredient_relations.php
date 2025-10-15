<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Ingredient;

echo "=== CORRECCIÓN DE RELACIONES PRODUCTOS-INGREDIENTES ===\n\n";

// Obtener productos que son ingredientes pero no tienen registro en la tabla ingredients
$ingredientProducts = Product::where('product_type', 'ingredient')
    ->orWhere('product_type', 'both')
    ->get();

$createdIngredients = 0;
$errors = [];

foreach ($ingredientProducts as $product) {
    // Verificar si ya existe un ingrediente con el mismo código
    $existingIngredient = Ingredient::where('code', $product->code)->first();
    
    if (!$existingIngredient) {
        echo "Creando ingrediente para producto: {$product->name} (Código: {$product->code})\n";
        
        try {
            $ingredient = Ingredient::create([
                'name' => $product->name,
                'code' => $product->code,
                'description' => $product->description ?? 'Ingrediente creado automáticamente',
                'unit_of_measure' => 'unidad', // Valor por defecto
                'min_stock' => 10, // Valor por defecto
                'current_stock' => $product->current_stock ?? 0,
                'current_cost' => $product->current_cost ?? 0,
                'supplier_id' => null, // Se puede asignar después
                'active' => true
            ]);
            
            echo "✓ Ingrediente creado con ID: {$ingredient->id}\n";
            $createdIngredients++;
            
        } catch (Exception $e) {
            $error = "Error al crear ingrediente para {$product->name}: " . $e->getMessage();
            echo "✗ {$error}\n";
            $errors[] = $error;
        }
    } else {
        echo "✓ Ingrediente ya existe para producto: {$product->name} (ID: {$existingIngredient->id})\n";
    }
    echo "---\n";
}

echo "\n=== RESUMEN DE LA CORRECCIÓN ===\n";
echo "Ingredientes creados: {$createdIngredients}\n";
echo "Errores encontrados: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrores:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
}

echo "\n=== VERIFICACIÓN FINAL ===\n";
$totalProducts = $ingredientProducts->count();
$totalIngredients = Ingredient::count();
$missingIngredients = 0;

foreach ($ingredientProducts as $product) {
    if (!Ingredient::where('code', $product->code)->exists()) {
        $missingIngredients++;
    }
}

echo "Total productos ingredientes: {$totalProducts}\n";
echo "Total ingredientes en BD: {$totalIngredients}\n";
echo "Productos sin ingrediente: {$missingIngredients}\n";

if ($missingIngredients == 0) {
    echo "\n🎉 ¡Todas las relaciones han sido corregidas!\n";
} else {
    echo "\n⚠️  Aún hay {$missingIngredients} productos sin ingrediente correspondiente.\n";
}