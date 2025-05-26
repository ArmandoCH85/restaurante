<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ListProductsWithRecipes extends Command
{
    protected $signature = 'products:list-recipes';
    protected $description = 'Listar productos con recetas y sus ingredientes';

    public function handle()
    {
        $products = Product::where('has_recipe', true)
            ->with(['recipe.details.ingredient'])
            ->get();

        if ($products->isEmpty()) {
            $this->error('No se encontraron productos con recetas');
            return 1;
        }

        $this->info("📋 PRODUCTOS CON RECETAS:");
        $this->line("");

        foreach ($products as $product) {
            $this->line("🍽️  {$product->name} (ID: {$product->id})");
            $this->line("   💰 Precio actual: S/ " . number_format($product->sale_price, 2));
            
            if ($product->recipe) {
                $this->line("   📝 Receta ID: {$product->recipe->id}");
                $this->line("   💵 Costo esperado: S/ " . number_format($product->recipe->expected_cost, 2));
                $this->line("   ⏱️  Tiempo preparación: {$product->recipe->preparation_time} min");
                $this->line("   🧾 Ingredientes ({$product->recipe->details->count()}):");
                
                foreach ($product->recipe->details as $detail) {
                    if ($detail->ingredient) {
                        $itemCost = $detail->quantity * ($detail->ingredient->current_cost ?? 0);
                        $this->line("      • {$detail->ingredient->name}: {$detail->quantity} {$detail->unit_of_measure}");
                        $this->line("        Costo unitario: S/ " . number_format($detail->ingredient->current_cost ?? 0, 2));
                        $this->line("        Costo total: S/ " . number_format($itemCost, 2));
                    } else {
                        $this->line("      • [Ingrediente no encontrado - ID: {$detail->ingredient_id}]");
                    }
                }
            } else {
                $this->line("   ❌ Receta no encontrada");
            }
            
            $this->line("   " . str_repeat("─", 50));
            $this->line("");
        }

        return 0;
    }
}
