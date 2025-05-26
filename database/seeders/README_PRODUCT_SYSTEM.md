# Product System Factories and Seeders

This document describes the comprehensive factories and seeders created for the restaurant product management system.

## Overview

The product system includes factories and seeders for all product-related models with realistic restaurant data in Spanish, proper relationships, and FIFO inventory management.

## Factories Created

### 1. ProductFactory
- **Location**: `database/factories/ProductFactory.php`
- **Features**:
  - Supports all product types: `ingredient`, `sale_item`, `both`
  - Realistic Spanish product names and descriptions
  - Proper pricing logic based on product type
  - State methods: `ingredient()`, `saleItem()`, `both()`, `beverage()`, `food()`

### 2. ProductCategoryFactory
- **Location**: `database/factories/ProductCategoryFactory.php`
- **Features**:
  - Hierarchical category support (parent/child)
  - Restaurant-specific categories
  - State methods: `beverages()`, `mainDishes()`, `appetizers()`, `desserts()`, `ingredients()`

### 3. SupplierFactory
- **Location**: `database/factories/SupplierFactory.php`
- **Features**:
  - Realistic Peruvian business names and tax IDs
  - Specialized supplier types
  - State methods: `meatSupplier()`, `vegetableSupplier()`, `seafoodSupplier()`, `dairySupplier()`

### 4. IngredientFactory
- **Location**: `database/factories/IngredientFactory.php`
- **Features**:
  - Common restaurant ingredients
  - Proper units of measure and pricing
  - State methods: `meat()`, `vegetable()`, `seafood()`, `dairy()`, `spice()`, `grain()`, `oil()`

### 5. WarehouseFactory
- **Location**: `database/factories/WarehouseFactory.php`
- **Features**:
  - Restaurant-specific warehouse types
  - Default warehouse support
  - State methods: `default()`, `kitchen()`, `beverages()`, `freezer()`, `refrigerator()`

### 6. RecipeFactory
- **Location**: `database/factories/RecipeFactory.php`
- **Features**:
  - Detailed cooking instructions in Spanish
  - Realistic preparation times and costs
  - State methods: `simple()`, `complex()`, `beverage()`, `dessert()`, `mainDish()`

### 7. RecipeDetailFactory
- **Location**: `database/factories/RecipeDetailFactory.php`
- **Features**:
  - Ingredient quantities for different dish types
  - Proper units of measure
  - State methods: `forMeatDish()`, `forChickenDish()`, `forSeafoodDish()`, `forVegetarianDish()`

### 8. IngredientStockFactory
- **Location**: `database/factories/IngredientStockFactory.php`
- **Features**:
  - FIFO inventory management
  - Expiry date handling
  - Stock status management
  - State methods: `available()`, `reserved()`, `expired()`, `fresh()`, `nearExpiry()`

## Seeders Created

### 1. ProductCategorySeeder
- **Location**: `database/seeders/ProductCategorySeeder.php`
- **Creates**:
  - Main categories: Bebidas, Entradas, Platos Principales, Acompañamientos, Postres, Snacks, Ingredientes
  - Subcategories for each main category
  - Proper hierarchy and display order

### 2. SupplierSeeder
- **Location**: `database/seeders/SupplierSeeder.php`
- **Creates**:
  - 15 realistic suppliers for different product types
  - Complete contact information
  - Specialized suppliers (meat, vegetables, seafood, dairy, etc.)

### 3. WarehouseSeeder
- **Location**: `database/seeders/WarehouseSeeder.php`
- **Creates**:
  - 9 warehouses including default warehouse
  - Kitchen, freezer, refrigerator, dry goods storage
  - Proper location descriptions

### 4. IngredientSeeder
- **Location**: `database/seeders/IngredientSeeder.php`
- **Creates**:
  - 25+ common restaurant ingredients
  - Proper categorization and supplier relationships
  - Realistic stock levels and costs

### 5. ProductSeeder
- **Location**: `database/seeders/ProductSeeder.php`
- **Creates**:
  - Traditional Peruvian dishes and beverages
  - Products of all types (ingredient, sale_item, both)
  - Proper categorization and pricing

### 6. RecipeSeeder
- **Location**: `database/seeders/RecipeSeeder.php`
- **Creates**:
  - Detailed recipes for products with `has_recipe = true`
  - Step-by-step cooking instructions
  - Recipe details with ingredient quantities

### 7. IngredientStockSeeder
- **Location**: `database/seeders/IngredientStockSeeder.php`
- **Creates**:
  - Multiple stock entries per ingredient for FIFO demonstration
  - Realistic expiry dates based on ingredient type
  - Different warehouses and purchase dates

### 8. ProductSystemSeeder (Master Seeder)
- **Location**: `database/seeders/ProductSystemSeeder.php`
- **Purpose**: Runs all product-related seeders in the correct order

## Usage Instructions

### Running All Seeders
```bash
php artisan db:seed --class=ProductSystemSeeder
```

### Running Individual Seeders
```bash
php artisan db:seed --class=ProductCategorySeeder
php artisan db:seed --class=SupplierSeeder
php artisan db:seed --class=WarehouseSeeder
php artisan db:seed --class=IngredientSeeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=RecipeSeeder
php artisan db:seed --class=IngredientStockSeeder
```

### Using Factories in Tests
```php
// Create a product with recipe
$product = Product::factory()->saleItem()->withRecipe()->create();

// Create ingredients for a meat dish
$ingredients = Ingredient::factory()->meat()->count(5)->create();

// Create stock entries with FIFO
$stock = IngredientStock::factory()->fresh()->count(3)->create();

// Create a complete recipe with details
$recipe = Recipe::factory()->mainDish()->create();
RecipeDetail::factory()->forMeatDish()->count(5)->create(['recipe_id' => $recipe->id]);
```

## Data Structure

### Product Types
- **Ingredient**: `product_type = 'ingredient'`, `sale_price = 0`
- **Sale Item**: `product_type = 'sale_item'`, has sale price
- **Both**: `product_type = 'both'`, can be sold or used as ingredient

### Categories Created
1. **Bebidas** (Beverages)
   - Bebidas Frías, Bebidas Calientes, Jugos Naturales, Cervezas
2. **Entradas** (Appetizers)
   - Entradas Frías, Entradas Calientes, Sopas
3. **Platos Principales** (Main Dishes)
   - Comida Criolla, Carnes, Aves, Pescados y Mariscos, Pastas, Comida China
4. **Acompañamientos** (Side Dishes)
   - Arroces, Ensaladas, Papas
5. **Postres** (Desserts)
   - Postres Tradicionales, Postres Internacionales, Helados
6. **Snacks**
   - Sandwiches, Hamburguesas, Pizzas
7. **Ingredientes** (Ingredients)
   - Multiple subcategories for different ingredient types

### Sample Products Created
- **Beverages**: Inca Kola, Chicha Morada, Limonada
- **Main Dishes**: Lomo Saltado, Ají de Gallina, Arroz con Pollo, Ceviche, Pollo a la Brasa
- **Appetizers**: Papa a la Huancaína, Anticuchos, Causa Limeña
- **Desserts**: Suspiro Limeño, Mazamorra Morada
- **Ingredients**: Various meats, vegetables, spices, etc.

## Testing the System

After running the seeders, you can test:

1. **Product Management**: Create, edit, and manage products
2. **Recipe System**: View recipes and their ingredients
3. **Inventory Management**: Test FIFO stock consumption
4. **Category Hierarchy**: Navigate through category structure
5. **Supplier Relationships**: Manage supplier-ingredient relationships

## Notes

- All data is in Spanish for authentic restaurant experience
- Pricing is realistic for Peruvian market
- FIFO system is properly implemented with multiple stock entries
- Relationships are properly maintained between all models
- Stock levels and expiry dates are realistic for each ingredient type

## Troubleshooting

If you encounter issues:

1. Ensure all migrations are run: `php artisan migrate`
2. Check that User model exists (required for warehouse created_by)
3. Run seeders in order if running individually
4. Clear cache if needed: `php artisan cache:clear`

## Extending the System

To add more data:

1. Modify the arrays in seeders to add more items
2. Create new factory states for specific use cases
3. Add new categories or suppliers as needed
4. Extend recipe instructions for more complex dishes
