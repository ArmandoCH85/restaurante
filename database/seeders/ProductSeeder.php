<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $beverageCategory = ProductCategory::where('name', 'Bebidas Frías')->first() ?? ProductCategory::where('name', 'Bebidas')->first();
        $mainDishCategory = ProductCategory::where('name', 'Comida Criolla')->first() ?? ProductCategory::where('name', 'Platos Principales')->first();
        $appetizerCategory = ProductCategory::where('name', 'Entradas Calientes')->first() ?? ProductCategory::where('name', 'Entradas')->first();
        $dessertCategory = ProductCategory::where('name', 'Postres Tradicionales')->first() ?? ProductCategory::where('name', 'Postres')->first();
        $ingredientCategory = ProductCategory::where('name', 'Carnes y Aves')->first() ?? ProductCategory::where('name', 'Ingredientes')->first();

        // Fallback to first category if none found
        $defaultCategory = ProductCategory::first();

        $products = [
            // Bebidas (Sale Items)
            [
                'code' => 'BEB001',
                'name' => 'Inca Kola',
                'description' => 'Gaseosa Inca Kola 500ml',
                'sale_price' => 4.50,
                'current_cost' => 2.20,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $beverageCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => false,
                'available' => true,
            ],
            [
                'code' => 'BEB002',
                'name' => 'Chicha Morada',
                'description' => 'Chicha morada tradicional preparada en casa',
                'sale_price' => 6.00,
                'current_cost' => 2.80,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $beverageCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'BEB003',
                'name' => 'Limonada',
                'description' => 'Limonada fresca con hielo y menta',
                'sale_price' => 5.50,
                'current_cost' => 1.80,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $beverageCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],

            // Platos Principales (Sale Items)
            [
                'code' => 'PLT001',
                'name' => 'Lomo Saltado',
                'description' => 'Delicioso lomo saltado con papas fritas y arroz',
                'sale_price' => 28.00,
                'current_cost' => 16.50,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $mainDishCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'PLT002',
                'name' => 'Ají de Gallina',
                'description' => 'Tradicional ají de gallina con papa sancochada',
                'sale_price' => 24.00,
                'current_cost' => 14.20,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $mainDishCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'PLT003',
                'name' => 'Arroz con Pollo',
                'description' => 'Arroz con pollo cilantrado estilo casero',
                'sale_price' => 22.00,
                'current_cost' => 12.80,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $mainDishCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'PLT004',
                'name' => 'Ceviche de Pescado',
                'description' => 'Ceviche fresco con pescado del día',
                'sale_price' => 32.00,
                'current_cost' => 18.50,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $mainDishCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'PLT005',
                'name' => 'Pollo a la Brasa',
                'description' => 'Pollo a la brasa con papas fritas y ensalada',
                'sale_price' => 35.00,
                'current_cost' => 20.00,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $mainDishCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],

            // Entradas (Sale Items)
            [
                'code' => 'ENT001',
                'name' => 'Papa a la Huancaína',
                'description' => 'Papa sancochada con salsa huancaína',
                'sale_price' => 12.00,
                'current_cost' => 6.50,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'ENT002',
                'name' => 'Anticuchos',
                'description' => 'Anticuchos de corazón con papa y choclo',
                'sale_price' => 18.00,
                'current_cost' => 10.20,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'ENT003',
                'name' => 'Causa Limeña',
                'description' => 'Causa limeña rellena de pollo',
                'sale_price' => 15.00,
                'current_cost' => 8.50,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],

            // Postres (Sale Items)
            [
                'code' => 'POS001',
                'name' => 'Suspiro Limeño',
                'description' => 'Tradicional suspiro limeño casero',
                'sale_price' => 8.00,
                'current_cost' => 4.20,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $dessertCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'POS002',
                'name' => 'Mazamorra Morada',
                'description' => 'Mazamorra morada con arroz con leche',
                'sale_price' => 7.00,
                'current_cost' => 3.50,
                'current_stock' => 0,
                'product_type' => Product::TYPE_SALE_ITEM,
                'category_id' => $dessertCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],

            // Productos tipo "Both" (pueden venderse o usarse como ingredientes)
            [
                'code' => 'BOT001',
                'name' => 'Salsa Criolla',
                'description' => 'Salsa criolla fresca para acompañar',
                'sale_price' => 3.50,
                'current_cost' => 1.20,
                'current_stock' => 15.500,
                'product_type' => Product::TYPE_BOTH,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'BOT002',
                'name' => 'Salsa Huancaína',
                'description' => 'Salsa huancaína casera',
                'sale_price' => 4.00,
                'current_cost' => 1.80,
                'current_stock' => 12.250,
                'product_type' => Product::TYPE_BOTH,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => true,
                'available' => true,
            ],
            [
                'code' => 'BOT003',
                'name' => 'Pan Francés',
                'description' => 'Pan francés fresco del día',
                'sale_price' => 0.50,
                'current_cost' => 0.20,
                'current_stock' => 85.000,
                'product_type' => Product::TYPE_BOTH,
                'category_id' => $appetizerCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => false,
                'available' => true,
            ],

            // Ingredientes (Ingredient Type)
            [
                'code' => 'ING001',
                'name' => 'Pollo Trozado',
                'description' => 'Pollo trozado para preparaciones',
                'sale_price' => 0,
                'current_cost' => 18.50,
                'current_stock' => 25.500,
                'product_type' => Product::TYPE_INGREDIENT,
                'category_id' => $ingredientCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => false,
                'available' => true,
            ],
            [
                'code' => 'ING002',
                'name' => 'Carne para Lomo',
                'description' => 'Carne de res cortada para lomo saltado',
                'sale_price' => 0,
                'current_cost' => 32.00,
                'current_stock' => 15.750,
                'product_type' => Product::TYPE_INGREDIENT,
                'category_id' => $ingredientCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => false,
                'available' => true,
            ],
            [
                'code' => 'ING003',
                'name' => 'Pescado Fileteado',
                'description' => 'Pescado fresco fileteado para ceviche',
                'sale_price' => 0,
                'current_cost' => 28.00,
                'current_stock' => 8.500,
                'product_type' => Product::TYPE_INGREDIENT,
                'category_id' => $ingredientCategory?->id ?? $defaultCategory->id,
                'active' => true,
                'has_recipe' => false,
                'available' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Products seeded successfully!');
    }
}
