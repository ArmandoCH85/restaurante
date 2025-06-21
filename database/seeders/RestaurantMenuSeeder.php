<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class RestaurantMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías Principales
        $bebidas = ProductCategory::create([
            'name' => 'Bebidas',
            'description' => 'Bebidas frías y calientes',
            'visible_in_menu' => true,
            'display_order' => 1,
        ]);

        $platos = ProductCategory::create([
            'name' => 'Platos Principales',
            'description' => 'Platos principales del menú',
            'visible_in_menu' => true,
            'display_order' => 2,
        ]);

        $entradas = ProductCategory::create([
            'name' => 'Entradas',
            'description' => 'Aperitivos y entradas',
            'visible_in_menu' => true,
            'display_order' => 3,
        ]);

        $postres = ProductCategory::create([
            'name' => 'Postres',
            'description' => 'Postres y dulces',
            'visible_in_menu' => true,
            'display_order' => 4,
        ]);

        // Subcategorías de Bebidas
        $bebidasFrias = ProductCategory::create([
            'name' => 'Bebidas Frías',
            'description' => 'Refrescos y bebidas heladas',
            'parent_category_id' => $bebidas->id,
            'visible_in_menu' => true,
            'display_order' => 1,
        ]);

        $bebidasCalientes = ProductCategory::create([
            'name' => 'Bebidas Calientes',
            'description' => 'Cafés, tés e infusiones',
            'parent_category_id' => $bebidas->id,
            'visible_in_menu' => true,
            'display_order' => 2,
        ]);

        // Subcategorías de Platos Principales
        $carnes = ProductCategory::create([
            'name' => 'Carnes',
            'description' => 'Platos con carne',
            'parent_category_id' => $platos->id,
            'visible_in_menu' => true,
            'display_order' => 1,
        ]);

        $pescados = ProductCategory::create([
            'name' => 'Pescados',
            'description' => 'Platos con pescado',
            'parent_category_id' => $platos->id,
            'visible_in_menu' => true,
            'display_order' => 2,
        ]);

        // Productos - Bebidas Frías
        Product::create([
            'name' => 'Coca Cola',
            'description' => 'Gaseosa Coca Cola 500ml',
            'category_id' => $bebidasFrias->id,
            'sale_price' => 3.50,
            'current_cost' => 2.00,
            'stock' => 100,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Limonada',
            'description' => 'Limonada fresca',
            'category_id' => $bebidasFrias->id,
            'sale_price' => 5.00,
            'current_cost' => 2.50,
            'stock' => 50,
            'visible_in_menu' => true,
        ]);

        // Productos - Bebidas Calientes
        Product::create([
            'name' => 'Café Americano',
            'description' => 'Café negro americano',
            'category_id' => $bebidasCalientes->id,
            'sale_price' => 4.00,
            'current_cost' => 1.50,
            'stock' => 100,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Té Verde',
            'description' => 'Té verde orgánico',
            'category_id' => $bebidasCalientes->id,
            'sale_price' => 3.50,
            'current_cost' => 1.00,
            'stock' => 100,
            'visible_in_menu' => true,
        ]);

        // Productos - Carnes
        Product::create([
            'name' => 'Lomo Saltado',
            'description' => 'Plato típico peruano con carne de res',
            'category_id' => $carnes->id,
            'sale_price' => 25.00,
            'current_cost' => 15.00,
            'stock' => 50,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Pollo a la Brasa',
            'description' => '1/4 de pollo a la brasa con papas',
            'category_id' => $carnes->id,
            'sale_price' => 20.00,
            'current_cost' => 12.00,
            'stock' => 50,
            'visible_in_menu' => true,
        ]);

        // Productos - Pescados
        Product::create([
            'name' => 'Ceviche',
            'description' => 'Ceviche de pescado fresco',
            'category_id' => $pescados->id,
            'sale_price' => 30.00,
            'current_cost' => 18.00,
            'stock' => 30,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Pescado Frito',
            'description' => 'Filete de pescado frito con guarnición',
            'category_id' => $pescados->id,
            'sale_price' => 25.00,
            'current_cost' => 15.00,
            'stock' => 30,
            'visible_in_menu' => true,
        ]);

        // Productos - Entradas
        Product::create([
            'name' => 'Tequeños',
            'description' => '6 tequeños de queso con guacamole',
            'category_id' => $entradas->id,
            'sale_price' => 15.00,
            'current_cost' => 8.00,
            'stock' => 50,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Causa Rellena',
            'description' => 'Causa rellena de pollo',
            'category_id' => $entradas->id,
            'sale_price' => 12.00,
            'current_cost' => 6.00,
            'stock' => 30,
            'visible_in_menu' => true,
        ]);

        // Productos - Postres
        Product::create([
            'name' => 'Tres Leches',
            'description' => 'Porción de torta tres leches',
            'category_id' => $postres->id,
            'sale_price' => 8.00,
            'current_cost' => 4.00,
            'stock' => 20,
            'visible_in_menu' => true,
        ]);

        Product::create([
            'name' => 'Suspiro a la Limeña',
            'description' => 'Postre tradicional peruano',
            'category_id' => $postres->id,
            'sale_price' => 10.00,
            'current_cost' => 5.00,
            'stock' => 20,
            'visible_in_menu' => true,
        ]);
    }
}
