<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recipe;
use App\Models\RecipeDetail;

class DemoWorkflow extends Command
{
    protected $signature = 'demo:workflow';
    protected $description = 'Demostrar el flujo de trabajo: Producto → Ingredientes → Receta';

    public function handle()
    {
        $this->info("🚀 DEMOSTRACIÓN DEL FLUJO DE TRABAJO RESTAURANTE");
        $this->line("");

        // FASE 1: Crear producto con precio final
        $this->info("📋 FASE 1: CREANDO PRODUCTO CON PRECIO FINAL");
        $this->line("(Como si fuera el primer día del restaurante)");
        $this->line("");

        $product = $this->createProductWithFinalPrice();
        $this->line("");

        // Simular pausa de tiempo
        $this->info("⏰ [SIMULANDO UNA SEMANA DESPUÉS...]");
        $this->line("");

        // FASE 2: Crear ingredientes con costos
        $this->info("📋 FASE 2: REGISTRANDO INGREDIENTES CON COSTOS");
        $this->line("(Después de negociar con proveedores)");
        $this->line("");

        $ingredients = $this->createIngredients();
        $this->line("");

        // FASE 3: Crear receta y vincular
        $this->info("📋 FASE 3: CREANDO RECETA Y VINCULANDO AL PRODUCTO");
        $this->line("(Después de definir las recetas con el chef)");
        $this->line("");

        $recipe = $this->createAndLinkRecipe($product, $ingredients);
        $this->line("");

        // FASE 4: Análisis final
        $this->info("📋 FASE 4: ANÁLISIS DE RENTABILIDAD");
        $this->showFinalAnalysis($product, $recipe);

        return 0;
    }

    private function createProductWithFinalPrice(): Product
    {
        // Buscar o crear categoría
        $category = ProductCategory::firstOrCreate(
            ['name' => 'Platos Principales'],
            ['description' => 'Platos principales del menú', 'visible_in_menu' => true]
        );

        // Crear producto con precio final (sin receta aún)
        $product = Product::create([
            'code' => 'PROD' . time(), // Código único basado en timestamp
            'name' => 'Arroz Chaufa Especial',
            'description' => 'Arroz chaufa con pollo, cerdo y camarones',
            'sale_price' => 25.00,  // Precio basado en competencia/mercado
            'current_cost' => 0,    // Aún no sabemos el costo real
            'product_type' => 'sale_item',
            'category_id' => $category->id,
            'has_recipe' => false,  // Inicialmente sin receta
            'active' => true,
            'available' => true
        ]);

        $this->line("✅ Producto creado: {$product->name}");
        $this->line("   💰 Precio establecido: S/ " . number_format($product->sale_price, 2));
        $this->line("   🏷️  Código: {$product->code}");
        $this->line("   📝 Estado receta: Sin receta (has_recipe = false)");

        return $product;
    }

    private function createIngredients(): array
    {
        // Crear categoría para ingredientes
        $category = ProductCategory::firstOrCreate(
            ['name' => 'Ingredientes'],
            ['description' => 'Ingredientes para cocina', 'visible_in_menu' => false]
        );

        $ingredientsData = [
            ['name' => 'Arroz Grano Largo', 'cost' => 3.50, 'unit' => 'kg'],
            ['name' => 'Pollo Deshuesado', 'cost' => 18.00, 'unit' => 'kg'],
            ['name' => 'Cerdo en Trozos', 'cost' => 22.00, 'unit' => 'kg'],
            ['name' => 'Camarones Medianos', 'cost' => 45.00, 'unit' => 'kg'],
            ['name' => 'Huevos', 'cost' => 0.50, 'unit' => 'unidad'],
            ['name' => 'Cebolla China', 'cost' => 8.00, 'unit' => 'kg'],
            ['name' => 'Salsa de Soya', 'cost' => 12.00, 'unit' => 'litro'],
            ['name' => 'Aceite Vegetal', 'cost' => 8.50, 'unit' => 'litro']
        ];

        $ingredients = [];

        foreach ($ingredientsData as $index => $data) {
            // Generar código único
            $code = 'DEMO' . str_pad($index + 100, 3, '0', STR_PAD_LEFT);

            $ingredient = Product::create([
                'code' => $code,
                'name' => $data['name'],
                'description' => "Ingrediente para cocina - {$data['unit']}",
                'sale_price' => 0,  // Los ingredientes no se venden directamente
                'current_cost' => $data['cost'],
                'current_stock' => rand(10, 50), // Stock simulado
                'product_type' => 'ingredient',
                'category_id' => $category->id,
                'has_recipe' => false,
                'active' => true
            ]);

            $ingredients[] = $ingredient;
            $this->line("✅ Ingrediente: {$ingredient->name} - S/ " . number_format($ingredient->current_cost, 2) . "/{$data['unit']}");
        }

        $this->line("");
        $this->line("📦 Total ingredientes creados: " . count($ingredients));

        return $ingredients;
    }

    private function createAndLinkRecipe(Product $product, array $ingredients): Recipe
    {
        // Crear la receta
        $recipe = Recipe::create([
            'product_id' => $product->id,
            'preparation_instructions' => 'Saltear el arroz con los ingredientes en wok caliente. Condimentar con salsa de soya.',
            'expected_cost' => 0, // Se calculará después
            'preparation_time' => 20.0
        ]);

        // Definir ingredientes y cantidades para el Arroz Chaufa
        $recipeIngredients = [
            'Arroz Grano Largo' => ['quantity' => 0.150, 'unit' => 'kg'],
            'Pollo Deshuesado' => ['quantity' => 0.080, 'unit' => 'kg'],
            'Cerdo en Trozos' => ['quantity' => 0.060, 'unit' => 'kg'],
            'Camarones Medianos' => ['quantity' => 0.040, 'unit' => 'kg'],
            'Huevos' => ['quantity' => 1.000, 'unit' => 'unidad'],
            'Cebolla China' => ['quantity' => 0.030, 'unit' => 'kg'],
            'Salsa de Soya' => ['quantity' => 0.020, 'unit' => 'litro'],
            'Aceite Vegetal' => ['quantity' => 0.015, 'unit' => 'litro']
        ];

        $this->line("📝 Agregando ingredientes a la receta:");

        foreach ($recipeIngredients as $ingredientName => $data) {
            $ingredient = collect($ingredients)->firstWhere('name', $ingredientName);

            if ($ingredient) {
                RecipeDetail::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $data['quantity'],
                    'unit_of_measure' => $data['unit']
                ]);

                $itemCost = $data['quantity'] * $ingredient->current_cost;
                $this->line("   ✅ {$ingredientName}: {$data['quantity']} {$data['unit']} - S/ " . number_format($itemCost, 2));
            }
        }

        // Calcular costo esperado
        $recipe->updateExpectedCost();
        $recipe->refresh();

        // Actualizar el producto para indicar que tiene receta
        $product->update([
            'has_recipe' => true,
            'current_cost' => $recipe->expected_cost  // Actualizar con costo real
        ]);

        $this->line("");
        $this->line("✅ Receta creada y vinculada al producto");
        $this->line("💵 Costo calculado: S/ " . number_format($recipe->expected_cost, 2));

        return $recipe;
    }

    private function showFinalAnalysis(Product $product, Recipe $recipe): void
    {
        $product->refresh();

        $this->line("🎯 ANÁLISIS FINAL DEL PRODUCTO:");
        $this->line("");
        $this->line("🍽️  Producto: {$product->name}");
        $this->line("💰 Precio de venta: S/ " . number_format($product->sale_price, 2));
        $this->line("💵 Costo real: S/ " . number_format($recipe->expected_cost, 2));

        $margin = (($product->sale_price - $recipe->expected_cost) / $product->sale_price) * 100;
        $this->line("📊 Margen bruto: " . number_format($margin, 1) . "%");

        $profit = $product->sale_price - $recipe->expected_cost;
        $this->line("💸 Ganancia por plato: S/ " . number_format($profit, 2));

        $this->line("");

        if ($margin < 30) {
            $this->error("⚠️  MARGEN BAJO: Considere aumentar el precio o reducir costos");
        } elseif ($margin > 70) {
            $this->info("💡 MARGEN ALTO: Podría reducir precio para ser más competitivo");
        } else {
            $this->info("✅ MARGEN SALUDABLE: El producto es rentable");
        }

        $this->line("");
        $this->info("🔄 FLUJO COMPLETADO EXITOSAMENTE:");
        $this->line("   1. ✅ Producto creado con precio de mercado");
        $this->line("   2. ✅ Ingredientes registrados con costos reales");
        $this->line("   3. ✅ Receta creada y vinculada");
        $this->line("   4. ✅ Análisis de rentabilidad realizado");

        $this->line("");
        $this->info("🧮 Comandos para seguir analizando:");
        $this->line("   php artisan products:calculate-prices {$product->id}");
        $this->line("   php artisan products:list-recipes");
    }
}
