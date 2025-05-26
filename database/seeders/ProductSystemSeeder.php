<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSystemSeeder extends Seeder
{
    /**
     * Run the product system database seeds in the correct order.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Product System Seeding...');

        // 1. Product Categories (no dependencies)
        $this->command->info('📂 Seeding Product Categories...');
        $this->call(ProductCategorySeeder::class);

        // 2. Suppliers (no dependencies)
        $this->command->info('🏢 Seeding Suppliers...');
        $this->call(SupplierSeeder::class);

        // 3. Warehouses (no dependencies)
        $this->command->info('🏪 Seeding Warehouses...');
        $this->call(WarehouseSeeder::class);

        // 4. Ingredients (depends on Suppliers)
        $this->command->info('🥕 Seeding Ingredients...');
        $this->call(IngredientSeeder::class);

        // 5. Products (depends on ProductCategories)
        $this->command->info('🍽️ Seeding Products...');
        $this->call(ProductSeeder::class);

        // 6. Recipes (depends on Products)
        $this->command->info('📝 Seeding Recipes...');
        $this->call(RecipeSeeder::class);

        // 7. Ingredient Stock (depends on Ingredients and Warehouses)
        $this->command->info('📦 Seeding Ingredient Stock...');
        $this->call(IngredientStockSeeder::class);

        $this->command->info('✅ Product System Seeding completed successfully!');
        $this->command->line('');
        $this->command->info('📊 Summary of seeded data:');
        $this->command->line('   • Product Categories with hierarchical structure');
        $this->command->line('   • Suppliers for different ingredient types');
        $this->command->line('   • Warehouses including default warehouse');
        $this->command->line('   • Common restaurant ingredients');
        $this->command->line('   • Restaurant products (dishes, beverages, etc.)');
        $this->command->line('   • Recipes with detailed instructions');
        $this->command->line('   • Ingredient stock with FIFO entries');
        $this->command->line('');
        $this->command->info('🎯 Your restaurant system is now ready for testing!');
    }
}
