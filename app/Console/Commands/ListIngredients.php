<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class ListIngredients extends Command
{
    protected $signature = 'products:list-ingredients';
    protected $description = 'Listar productos que pueden ser ingredientes';

    public function handle()
    {
        $ingredients = Product::where('product_type', 'ingredient')
            ->orWhere('product_type', 'both')
            ->orderBy('name')
            ->get();

        if ($ingredients->isEmpty()) {
            $this->error('No se encontraron productos tipo ingrediente');
            return 1;
        }

        $this->info("🧄 PRODUCTOS DISPONIBLES COMO INGREDIENTES:");
        $this->line("");

        foreach ($ingredients as $ingredient) {
            $this->line("ID: {$ingredient->id} | {$ingredient->name}");
            $this->line("   💰 Costo actual: S/ " . number_format($ingredient->current_cost, 2));
            $this->line("   📦 Stock actual: " . number_format($ingredient->current_stock, 3));
            $this->line("   🏷️  Tipo: {$ingredient->product_type}");
            $this->line("");
        }

        return 0;
    }
}
