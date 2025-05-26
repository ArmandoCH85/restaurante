<?php

namespace Database\Seeders;

use App\Models\IngredientStock;
use App\Models\Ingredient;
use App\Models\Warehouse;
use App\Models\Purchase;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class IngredientStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = Ingredient::all();
        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        $warehouses = Warehouse::where('active', true)->get();

        if ($ingredients->isEmpty() || $warehouses->isEmpty()) {
            $this->command->warn('No ingredients or warehouses found. Please run IngredientSeeder and WarehouseSeeder first.');
            return;
        }

        foreach ($ingredients as $ingredient) {
            $this->createStockForIngredient($ingredient, $warehouses, $defaultWarehouse);
        }

        $this->command->info('Ingredient stock seeded successfully!');
    }

    private function createStockForIngredient(Ingredient $ingredient, $warehouses, $defaultWarehouse): void
    {
        // Create multiple stock entries for FIFO demonstration
        $stockEntries = $this->getStockEntriesForIngredient($ingredient);

        foreach ($stockEntries as $entry) {
            $warehouse = $entry['warehouse'] === 'default' ? $defaultWarehouse : $warehouses->random();
            
            IngredientStock::create([
                'ingredient_id' => $ingredient->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $entry['quantity'],
                'unit_cost' => $entry['unit_cost'],
                'expiry_date' => $entry['expiry_date'],
                'status' => $entry['status'],
                'purchase_id' => null, // We'll create purchases separately
                'created_at' => $entry['created_at'],
                'updated_at' => $entry['created_at'],
            ]);
        }
    }

    private function getStockEntriesForIngredient(Ingredient $ingredient): array
    {
        $baseQuantity = $ingredient->current_stock / 3; // Divide into 3 entries
        $baseCost = $ingredient->current_cost;

        return [
            // Older stock (FIFO - should be consumed first)
            [
                'quantity' => round($baseQuantity * 0.4, 3),
                'unit_cost' => round($baseCost * 0.9, 2), // Slightly cheaper (older purchase)
                'expiry_date' => $this->getExpiryDateByType($ingredient->name, 'old'),
                'status' => IngredientStock::STATUS_AVAILABLE,
                'warehouse' => 'default',
                'created_at' => Carbon::now()->subDays(45),
            ],
            // Medium age stock
            [
                'quantity' => round($baseQuantity * 0.35, 3),
                'unit_cost' => $baseCost,
                'expiry_date' => $this->getExpiryDateByType($ingredient->name, 'medium'),
                'status' => IngredientStock::STATUS_AVAILABLE,
                'warehouse' => 'default',
                'created_at' => Carbon::now()->subDays(20),
            ],
            // Recent stock (FIFO - should be consumed last)
            [
                'quantity' => round($baseQuantity * 0.25, 3),
                'unit_cost' => round($baseCost * 1.1, 2), // Slightly more expensive (recent purchase)
                'expiry_date' => $this->getExpiryDateByType($ingredient->name, 'new'),
                'status' => IngredientStock::STATUS_AVAILABLE,
                'warehouse' => 'random',
                'created_at' => Carbon::now()->subDays(5),
            ],
        ];
    }

    private function getExpiryDateByType(string $ingredientName, string $ageType): string
    {
        $perishableIngredients = [
            'Pollo', 'Carne', 'Cerdo', 'Pescado', 'Camarones', 'Leche', 'Queso', 'Mantequilla'
        ];

        $semiPerishable = [
            'Tomate', 'Cebolla', 'Papa', 'Ajo', 'Cilantro', 'Huevos'
        ];

        $nonPerishable = [
            'Arroz', 'Frijoles', 'Harina', 'Azúcar', 'Sal', 'Pimienta', 'Comino', 'Aceite'
        ];

        $isPerishable = collect($perishableIngredients)->contains(function ($item) use ($ingredientName) {
            return str_contains($ingredientName, $item);
        });

        $isSemiPerishable = collect($semiPerishable)->contains(function ($item) use ($ingredientName) {
            return str_contains($ingredientName, $item);
        });

        if ($isPerishable) {
            return match ($ageType) {
                'old' => Carbon::now()->addDays(rand(1, 5))->format('Y-m-d'),
                'medium' => Carbon::now()->addDays(rand(3, 8))->format('Y-m-d'),
                'new' => Carbon::now()->addDays(rand(5, 12))->format('Y-m-d'),
            };
        } elseif ($isSemiPerishable) {
            return match ($ageType) {
                'old' => Carbon::now()->addDays(rand(7, 21))->format('Y-m-d'),
                'medium' => Carbon::now()->addDays(rand(14, 35))->format('Y-m-d'),
                'new' => Carbon::now()->addDays(rand(21, 60))->format('Y-m-d'),
            };
        } else {
            // Non-perishable
            return match ($ageType) {
                'old' => Carbon::now()->addMonths(rand(6, 12))->format('Y-m-d'),
                'medium' => Carbon::now()->addMonths(rand(8, 18))->format('Y-m-d'),
                'new' => Carbon::now()->addMonths(rand(12, 24))->format('Y-m-d'),
            };
        }
    }
}
