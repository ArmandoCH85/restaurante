<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeDetail;

class CreateSampleRecipe extends Command
{
    protected $signature = 'recipes:create-sample';
    protected $description = 'Crear una receta de ejemplo para Lomo Saltado';

    public function handle()
    {
        // Buscar el producto Lomo Saltado
        $lomoSaltado = Product::where('name', 'like', '%Lomo Saltado%')->first();
        
        if (!$lomoSaltado) {
            $this->error('Producto Lomo Saltado no encontrado');
            return 1;
        }

        // Verificar si ya tiene receta
        if ($lomoSaltado->recipe) {
            $this->info('El Lomo Saltado ya tiene una receta. Eliminando la anterior...');
            $lomoSaltado->recipe->details()->delete();
            $lomoSaltado->recipe->delete();
        }

        // Crear la receta
        $recipe = Recipe::create([
            'product_id' => $lomoSaltado->id,
            'preparation_instructions' => 'Saltear la carne con cebolla, tomate y papas fritas. Servir con arroz.',
            'expected_cost' => 0, // Se calculará después
            'preparation_time' => 25.0
        ]);

        $this->info("✅ Receta creada para {$lomoSaltado->name}");

        // Agregar ingredientes a la receta
        $ingredients = [
            ['name' => 'Carne para Lomo', 'quantity' => 0.200, 'unit' => 'kg'],
            ['name' => 'Pan Francés', 'quantity' => 1.000, 'unit' => 'unidad'], // Para acompañar
        ];

        foreach ($ingredients as $ingredientData) {
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

                $this->line("   ✅ Agregado: {$ingredient->name} - {$ingredientData['quantity']} {$ingredientData['unit']}");
            } else {
                $this->error("   ❌ Ingrediente no encontrado: {$ingredientData['name']}");
            }
        }

        // Calcular y actualizar el costo esperado
        $recipe->updateExpectedCost();
        $recipe->refresh();

        $this->line("");
        $this->info("📊 RECETA CREADA EXITOSAMENTE:");
        $this->line("   🍽️  Producto: {$lomoSaltado->name}");
        $this->line("   💰 Precio actual: S/ " . number_format($lomoSaltado->sale_price, 2));
        $this->line("   💵 Costo calculado: S/ " . number_format($recipe->expected_cost, 2));
        $this->line("   📊 Margen actual: " . number_format((($lomoSaltado->sale_price - $recipe->expected_cost) / $lomoSaltado->sale_price) * 100, 1) . "%");
        $this->line("   ⏱️  Tiempo preparación: {$recipe->preparation_time} minutos");

        $this->line("");
        $this->info("🧮 Ahora puedes usar:");
        $this->line("   php artisan products:calculate-prices {$lomoSaltado->id}");

        return 0;
    }
}
