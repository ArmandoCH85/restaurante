<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeDetail;

class CreateAnticuchosRecipe extends Command
{
    protected $signature = 'recipes:create-anticuchos';
    protected $description = 'Crear receta completa para Anticuchos como ejemplo';

    public function handle()
    {
        // Buscar el producto Anticuchos
        $anticuchos = Product::where('name', 'like', '%Anticuchos%')->first();
        
        if (!$anticuchos) {
            $this->error('Producto Anticuchos no encontrado');
            return 1;
        }

        $this->info("🍽️  CREANDO RECETA PARA: {$anticuchos->name}");
        $this->line("💰 Precio actual: S/ " . number_format($anticuchos->sale_price, 2));
        $this->line("");

        // Eliminar receta anterior si existe
        if ($anticuchos->recipe) {
            $this->info('Eliminando receta anterior...');
            $anticuchos->recipe->details()->delete();
            $anticuchos->recipe->delete();
        }

        // Crear la receta
        $recipe = Recipe::create([
            'product_id' => $anticuchos->id,
            'preparation_instructions' => 'Marinar la carne en chicha de jora y especias por 2 horas. Ensartar en palitos y asar a la parrilla. Servir con pan y salsa criolla.',
            'expected_cost' => 0,
            'preparation_time' => 15.0
        ]);

        $this->info("✅ Receta base creada");

        // Definir ingredientes para anticuchos
        $recipeIngredients = [
            ['name' => 'Carne para Lomo', 'quantity' => 0.150, 'unit' => 'kg'],
            ['name' => 'Pan Francés', 'quantity' => 2.000, 'unit' => 'unidad'],
            ['name' => 'Salsa Criolla', 'quantity' => 0.050, 'unit' => 'kg'],
            ['name' => 'Aceite Vegetal', 'quantity' => 0.010, 'unit' => 'litro']
        ];

        $this->line("");
        $this->info("📝 Agregando ingredientes:");

        foreach ($recipeIngredients as $ingredientData) {
            // Buscar el ingrediente
            $ingredient = Product::where('name', 'like', '%' . $ingredientData['name'] . '%')
                ->where(function($query) {
                    $query->where('product_type', 'ingredient')
                          ->orWhere('product_type', 'both');
                })
                ->first();

            if ($ingredient) {
                RecipeDetail::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $ingredientData['quantity'],
                    'unit_of_measure' => $ingredientData['unit']
                ]);

                $itemCost = $ingredientData['quantity'] * $ingredient->current_cost;
                $this->line("   ✅ {$ingredient->name}: {$ingredientData['quantity']} {$ingredientData['unit']} - S/ " . number_format($itemCost, 2));
            } else {
                $this->error("   ❌ Ingrediente no encontrado: {$ingredientData['name']}");
            }
        }

        // Calcular y actualizar el costo esperado
        $recipe->updateExpectedCost();
        $recipe->refresh();

        // Actualizar el producto
        $anticuchos->update([
            'has_recipe' => true,
            'current_cost' => $recipe->expected_cost
        ]);

        $this->line("");
        $this->info("📊 RECETA COMPLETADA:");
        $this->line("🍽️  Producto: {$anticuchos->name}");
        $this->line("💰 Precio de venta: S/ " . number_format($anticuchos->sale_price, 2));
        $this->line("💵 Costo de ingredientes: S/ " . number_format($recipe->expected_cost, 2));
        
        if ($anticuchos->sale_price > 0) {
            $margin = (($anticuchos->sale_price - $recipe->expected_cost) / $anticuchos->sale_price) * 100;
            $this->line("📊 Margen bruto: " . number_format($margin, 1) . "%");
            
            $profit = $anticuchos->sale_price - $recipe->expected_cost;
            $this->line("💸 Ganancia por plato: S/ " . number_format($profit, 2));
        }
        
        $this->line("⏱️  Tiempo de preparación: {$recipe->preparation_time} minutos");

        $this->line("");
        $this->info("🧮 Ahora puedes analizar:");
        $this->line("   php artisan products:calculate-prices {$anticuchos->id}");
        $this->line("   php artisan products:calculate-prices {$anticuchos->id} --margin=60 --labor=20 --overhead=15");

        return 0;
    }
}
