<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories if needed (optional - uncomment if you want to reset)
        // ProductCategory::truncate();

        // Create main categories (parent categories)
        $mainCategories = [
            [
                'name' => 'Bebidas',
                'description' => 'Bebidas refrescantes, calientes y alcohólicas',
                'visible_in_menu' => true,
                'display_order' => 1,
                'subcategories' => [
                    ['name' => 'Bebidas Frías', 'description' => 'Gaseosas, jugos y bebidas refrescantes'],
                    ['name' => 'Bebidas Calientes', 'description' => 'Café, té e infusiones'],
                    ['name' => 'Jugos Naturales', 'description' => 'Jugos de frutas frescas'],
                    ['name' => 'Cervezas', 'description' => 'Cervezas nacionales e importadas'],
                ]
            ],
            [
                'name' => 'Entradas',
                'description' => 'Entradas y aperitivos para comenzar la comida',
                'visible_in_menu' => true,
                'display_order' => 2,
                'subcategories' => [
                    ['name' => 'Entradas Frías', 'description' => 'Causas, ceviches y ensaladas'],
                    ['name' => 'Entradas Calientes', 'description' => 'Anticuchos, empanadas y frituras'],
                    ['name' => 'Sopas', 'description' => 'Sopas y caldos tradicionales'],
                ]
            ],
            [
                'name' => 'Platos Principales',
                'description' => 'Platos principales de la carta del restaurante',
                'visible_in_menu' => true,
                'display_order' => 3,
                'subcategories' => [
                    ['name' => 'Comida Criolla', 'description' => 'Platos tradicionales peruanos'],
                    ['name' => 'Carnes', 'description' => 'Platos a base de carne de res, cerdo y cordero'],
                    ['name' => 'Aves', 'description' => 'Platos a base de pollo, pavo y otras aves'],
                    ['name' => 'Platos Marinos', 'description' => 'Platos a base de pescados y mariscos frescos'],
                    ['name' => 'Pastas', 'description' => 'Pastas italianas y fusión'],
                    ['name' => 'Comida China', 'description' => 'Platos de la cocina chino-peruana'],
                ]
            ],
            [
                'name' => 'Acompañamientos',
                'description' => 'Guarniciones y acompañamientos',
                'visible_in_menu' => true,
                'display_order' => 4,
                'subcategories' => [
                    ['name' => 'Arroces', 'description' => 'Diferentes tipos de arroz'],
                    ['name' => 'Ensaladas', 'description' => 'Ensaladas frescas y mixtas'],
                    ['name' => 'Papas', 'description' => 'Preparaciones a base de papa'],
                ]
            ],
            [
                'name' => 'Postres',
                'description' => 'Deliciosos postres para finalizar la comida',
                'visible_in_menu' => true,
                'display_order' => 5,
                'subcategories' => [
                    ['name' => 'Postres Tradicionales', 'description' => 'Postres peruanos clásicos'],
                    ['name' => 'Postres Internacionales', 'description' => 'Postres de cocina internacional'],
                    ['name' => 'Helados', 'description' => 'Helados y sorbetes'],
                ]
            ],
            [
                'name' => 'Snacks',
                'description' => 'Bocaditos y snacks ligeros',
                'visible_in_menu' => true,
                'display_order' => 6,
                'subcategories' => [
                    ['name' => 'Sandwiches', 'description' => 'Sandwiches y bocaditos'],
                    ['name' => 'Hamburguesas', 'description' => 'Hamburguesas gourmet'],
                    ['name' => 'Pizzas', 'description' => 'Pizzas artesanales'],
                ]
            ],
            [
                'name' => 'Ingredientes',
                'description' => 'Ingredientes y materias primas para la cocina',
                'visible_in_menu' => false,
                'display_order' => 100,
                'subcategories' => [
                    ['name' => 'Carnes y Aves', 'description' => 'Carnes frescas y aves'],
                    ['name' => 'Productos Marinos', 'description' => 'Pescados y mariscos frescos'],
                    ['name' => 'Verduras y Hortalizas', 'description' => 'Verduras frescas de temporada'],
                    ['name' => 'Frutas', 'description' => 'Frutas frescas y de temporada'],
                    ['name' => 'Lácteos', 'description' => 'Leche, quesos y derivados lácteos'],
                    ['name' => 'Granos y Cereales', 'description' => 'Arroz, quinua, avena y otros granos'],
                    ['name' => 'Condimentos y Especias', 'description' => 'Especias, hierbas y condimentos'],
                    ['name' => 'Aceites y Vinagres', 'description' => 'Aceites de cocina y vinagres'],
                    ['name' => 'Abarrotes', 'description' => 'Productos secos y enlatados'],
                ]
            ],
        ];

        foreach ($mainCategories as $categoryData) {
            // Create parent category (check if exists first)
            $parentCategory = ProductCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'description' => $categoryData['description'],
                    'parent_category_id' => null,
                    'visible_in_menu' => $categoryData['visible_in_menu'],
                    'display_order' => $categoryData['display_order'],
                ]
            );

            // Create subcategories if they exist
            if (isset($categoryData['subcategories'])) {
                $subOrder = 1;
                foreach ($categoryData['subcategories'] as $subcategoryData) {
                    ProductCategory::firstOrCreate(
                        ['name' => $subcategoryData['name']],
                        [
                            'description' => $subcategoryData['description'],
                            'parent_category_id' => $parentCategory->id,
                            'visible_in_menu' => $categoryData['visible_in_menu'],
                            'display_order' => ($categoryData['display_order'] * 10) + $subOrder,
                        ]
                    );
                    $subOrder++;
                }
            }
        }

        $this->command->info('Product categories seeded successfully!');
    }
}
