<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Recipe;

class CalculateSuggestedPrices extends Command
{
    protected $signature = 'products:calculate-prices {product_id?} {--margin=40} {--labor=15} {--overhead=10}';
    protected $description = 'Calcular precios de venta sugeridos basados en costos de ingredientes';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $marginPercent = (float) $this->option('margin'); // Margen de ganancia %
        $laborPercent = (float) $this->option('labor');   // Costo de mano de obra %
        $overheadPercent = (float) $this->option('overhead'); // Gastos generales %

        if ($productId) {
            $products = Product::where('id', $productId)->where('has_recipe', true)->with('recipe.details.ingredient')->get();
        } else {
            $products = Product::where('has_recipe', true)->with('recipe.details.ingredient')->get();
        }

        if ($products->isEmpty()) {
            $this->error('No se encontraron productos con recetas');
            return 1;
        }

        $this->info("🧮 CALCULADORA DE PRECIOS DE VENTA");
        $this->line("📊 Parámetros:");
        $this->line("   • Margen de ganancia: {$marginPercent}%");
        $this->line("   • Costo mano de obra: {$laborPercent}%");
        $this->line("   • Gastos generales: {$overheadPercent}%");
        $this->line("");

        $results = [];

        foreach ($products as $product) {
            if (!$product->recipe) {
                continue;
            }

            // Calcular costo de ingredientes
            $ingredientCost = $this->calculateIngredientCost($product->recipe);
            
            // Calcular costos adicionales
            $laborCost = $ingredientCost * ($laborPercent / 100);
            $overheadCost = $ingredientCost * ($overheadPercent / 100);
            $totalCost = $ingredientCost + $laborCost + $overheadCost;
            
            // Calcular precio de venta sugerido
            $suggestedPrice = $totalCost * (1 + $marginPercent / 100);
            
            // Calcular margen actual
            $currentPrice = $product->sale_price;
            $currentMargin = $currentPrice > 0 ? (($currentPrice - $totalCost) / $currentPrice) * 100 : 0;

            $results[] = [
                'product' => $product,
                'ingredient_cost' => $ingredientCost,
                'labor_cost' => $laborCost,
                'overhead_cost' => $overheadCost,
                'total_cost' => $totalCost,
                'suggested_price' => $suggestedPrice,
                'current_price' => $currentPrice,
                'current_margin' => $currentMargin,
                'price_difference' => $suggestedPrice - $currentPrice
            ];
        }

        // Mostrar resultados
        $this->displayResults($results);

        return 0;
    }

    private function calculateIngredientCost(Recipe $recipe): float
    {
        $cost = 0;
        
        foreach ($recipe->details as $detail) {
            if ($detail->ingredient) {
                $ingredientCost = $detail->ingredient->current_cost ?? 0;
                $itemCost = $detail->quantity * $ingredientCost;
                $cost += $itemCost;
            }
        }
        
        return $cost;
    }

    private function displayResults(array $results): void
    {
        $this->line("");
        $this->info("📋 RESULTADOS DEL ANÁLISIS DE PRECIOS:");
        $this->line("");

        foreach ($results as $result) {
            $product = $result['product'];
            
            $this->line("🍽️  {$product->name} (ID: {$product->id})");
            $this->line("   📦 Costo ingredientes: S/ " . number_format($result['ingredient_cost'], 2));
            $this->line("   👨‍🍳 Costo mano de obra: S/ " . number_format($result['labor_cost'], 2));
            $this->line("   🏢 Gastos generales: S/ " . number_format($result['overhead_cost'], 2));
            $this->line("   💰 Costo total: S/ " . number_format($result['total_cost'], 2));
            $this->line("");
            $this->line("   💵 Precio actual: S/ " . number_format($result['current_price'], 2));
            $this->line("   💡 Precio sugerido: S/ " . number_format($result['suggested_price'], 2));
            $this->line("   📊 Margen actual: " . number_format($result['current_margin'], 1) . "%");
            
            if ($result['price_difference'] > 0) {
                $this->line("   ⬆️  Diferencia: +S/ " . number_format($result['price_difference'], 2) . " (subir precio)");
            } elseif ($result['price_difference'] < 0) {
                $this->line("   ⬇️  Diferencia: S/ " . number_format($result['price_difference'], 2) . " (bajar precio)");
            } else {
                $this->line("   ✅ Precio óptimo");
            }
            
            $this->line("");
            $this->line("   📝 Desglose de ingredientes:");
            foreach ($product->recipe->details as $detail) {
                if ($detail->ingredient) {
                    $itemCost = $detail->quantity * $detail->ingredient->current_cost;
                    $this->line("      • {$detail->ingredient->name}: {$detail->quantity} x S/ " . 
                              number_format($detail->ingredient->current_cost, 2) . 
                              " = S/ " . number_format($itemCost, 2));
                }
            }
            
            $this->line("   " . str_repeat("─", 50));
            $this->line("");
        }

        // Resumen general
        $totalProducts = count($results);
        $avgCurrentMargin = collect($results)->avg('current_margin');
        $productsNeedIncrease = collect($results)->where('price_difference', '>', 0)->count();
        $productsNeedDecrease = collect($results)->where('price_difference', '<', 0)->count();

        $this->info("📊 RESUMEN GENERAL:");
        $this->line("   • Total productos analizados: {$totalProducts}");
        $this->line("   • Margen promedio actual: " . number_format($avgCurrentMargin, 1) . "%");
        $this->line("   • Productos que necesitan subir precio: {$productsNeedIncrease}");
        $this->line("   • Productos que necesitan bajar precio: {$productsNeedDecrease}");
    }
}
