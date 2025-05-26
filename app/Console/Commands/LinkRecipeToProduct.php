<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeDetail;

class LinkRecipeToProduct extends Command
{
    protected $signature = 'recipes:link-to-product {product_id} {--interactive}';
    protected $description = 'Vincular una receta a un producto existente';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $interactive = $this->option('interactive');

        // Buscar el producto
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Producto con ID {$productId} no encontrado");
            return 1;
        }

        $this->info("🍽️  VINCULANDO RECETA AL PRODUCTO: {$product->name}");
        $this->line("💰 Precio actual: S/ " . number_format($product->sale_price, 2));
        $this->line("");

        // Verificar si ya tiene receta
        if ($product->recipe) {
            if (!$this->confirm("El producto ya tiene una receta. ¿Desea reemplazarla?")) {
                return 0;
            }
            
            $this->info("Eliminando receta anterior...");
            $product->recipe->details()->delete();
            $product->recipe->delete();
        }

        // Crear la receta
        $preparationTime = $this->ask("⏱️  Tiempo de preparación (minutos)", "15");
        $instructions = $this->ask("📝 Instrucciones de preparación", "Preparar según receta estándar");

        $recipe = Recipe::create([
            'product_id' => $product->id,
            'preparation_instructions' => $instructions,
            'expected_cost' => 0, // Se calculará después
            'preparation_time' => (float) $preparationTime
        ]);

        $this->info("✅ Receta base creada");

        // Agregar ingredientes
        if ($interactive) {
            $this->addIngredientsInteractively($recipe);
        } else {
            $this->addIngredientsFromMenu($recipe);
        }

        // Calcular costo esperado
        $recipe->updateExpectedCost();
        $recipe->refresh();

        // Actualizar el producto para indicar que tiene receta
        $product->update(['has_recipe' => true]);

        // Mostrar resumen
        $this->showRecipeSummary($product, $recipe);

        return 0;
    }

    private function addIngredientsInteractively(Recipe $recipe): void
    {
        $this->info("🧄 AGREGANDO INGREDIENTES A LA RECETA");
        $this->line("Escriba 'fin' para terminar");
        $this->line("");

        while (true) {
            $ingredientName = $this->ask("Nombre del ingrediente (o 'fin' para terminar)");
            
            if (strtolower($ingredientName) === 'fin') {
                break;
            }

            // Buscar ingredientes que coincidan
            $ingredients = Product::where('name', 'like', "%{$ingredientName}%")
                ->where(function($query) {
                    $query->where('product_type', 'ingredient')
                          ->orWhere('product_type', 'both');
                })
                ->get();

            if ($ingredients->isEmpty()) {
                $this->error("No se encontraron ingredientes con ese nombre");
                continue;
            }

            if ($ingredients->count() > 1) {
                $this->line("Ingredientes encontrados:");
                foreach ($ingredients as $ing) {
                    $this->line("  {$ing->id}: {$ing->name} - S/ {$ing->current_cost}");
                }
                $ingredientId = $this->ask("Seleccione el ID del ingrediente");
                $ingredient = $ingredients->find($ingredientId);
            } else {
                $ingredient = $ingredients->first();
                $this->line("Ingrediente encontrado: {$ingredient->name}");
            }

            if (!$ingredient) {
                $this->error("Ingrediente no válido");
                continue;
            }

            $quantity = $this->ask("Cantidad necesaria", "1");
            $unit = $this->ask("Unidad de medida", "unidad");

            RecipeDetail::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ingredient->id,
                'quantity' => (float) $quantity,
                'unit_of_measure' => $unit
            ]);

            $itemCost = (float) $quantity * $ingredient->current_cost;
            $this->info("✅ Agregado: {$ingredient->name} - {$quantity} {$unit} - S/ " . number_format($itemCost, 2));
        }
    }

    private function addIngredientsFromMenu(Recipe $recipe): void
    {
        // Mostrar ingredientes disponibles
        $ingredients = Product::where(function($query) {
            $query->where('product_type', 'ingredient')
                  ->orWhere('product_type', 'both');
        })->orderBy('name')->get();

        if ($ingredients->isEmpty()) {
            $this->error("No hay ingredientes disponibles. Primero debe crear ingredientes.");
            return;
        }

        $this->info("🧄 INGREDIENTES DISPONIBLES:");
        foreach ($ingredients as $ingredient) {
            $this->line("  {$ingredient->id}: {$ingredient->name} - S/ " . number_format($ingredient->current_cost, 2));
        }
        $this->line("");

        while (true) {
            $ingredientId = $this->ask("ID del ingrediente (0 para terminar)", "0");
            
            if ($ingredientId == 0) {
                break;
            }

            $ingredient = $ingredients->find($ingredientId);
            if (!$ingredient) {
                $this->error("Ingrediente no encontrado");
                continue;
            }

            $quantity = $this->ask("Cantidad de {$ingredient->name}", "1");
            $unit = $this->ask("Unidad de medida", "unidad");

            RecipeDetail::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $ingredient->id,
                'quantity' => (float) $quantity,
                'unit_of_measure' => $unit
            ]);

            $itemCost = (float) $quantity * $ingredient->current_cost;
            $this->info("✅ Agregado: {$ingredient->name} - {$quantity} {$unit} - S/ " . number_format($itemCost, 2));
        }
    }

    private function showRecipeSummary(Product $product, Recipe $recipe): void
    {
        $this->line("");
        $this->info("📊 RESUMEN DE LA RECETA CREADA:");
        $this->line("🍽️  Producto: {$product->name}");
        $this->line("💰 Precio de venta: S/ " . number_format($product->sale_price, 2));
        $this->line("💵 Costo de ingredientes: S/ " . number_format($recipe->expected_cost, 2));
        
        if ($product->sale_price > 0) {
            $margin = (($product->sale_price - $recipe->expected_cost) / $product->sale_price) * 100;
            $this->line("📊 Margen bruto: " . number_format($margin, 1) . "%");
        }
        
        $this->line("⏱️  Tiempo de preparación: {$recipe->preparation_time} minutos");
        $this->line("");

        $this->info("📝 Ingredientes de la receta:");
        foreach ($recipe->details as $detail) {
            $itemCost = $detail->quantity * $detail->ingredient->current_cost;
            $this->line("   • {$detail->ingredient->name}: {$detail->quantity} {$detail->unit_of_measure} - S/ " . number_format($itemCost, 2));
        }

        $this->line("");
        $this->info("🧮 Comandos útiles:");
        $this->line("   php artisan products:calculate-prices {$product->id}");
        $this->line("   php artisan products:list-recipes");
    }
}
