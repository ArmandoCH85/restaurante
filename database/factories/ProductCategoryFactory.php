<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition()
    {
        return [
            'name' => $this->generateCategoryName(),
            'description' => $this->generateCategoryDescription(),
            'parent_category_id' => null,
            'visible_in_menu' => $this->faker->boolean(85),
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function parent()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_category_id' => null,
                'visible_in_menu' => true,
                'display_order' => $this->faker->numberBetween(1, 20),
            ];
        });
    }

    public function child()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_category_id' => ProductCategory::factory()->parent(),
                'display_order' => $this->faker->numberBetween(21, 100),
            ];
        });
    }

    public function beverages()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Bebidas',
                'description' => 'Bebidas refrescantes, calientes y alcohólicas',
                'visible_in_menu' => true,
                'display_order' => 1,
            ];
        });
    }

    public function mainDishes()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Platos Principales',
                'description' => 'Platos principales de la carta del restaurante',
                'visible_in_menu' => true,
                'display_order' => 2,
            ];
        });
    }

    public function appetizers()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Entradas',
                'description' => 'Entradas y aperitivos para comenzar la comida',
                'visible_in_menu' => true,
                'display_order' => 3,
            ];
        });
    }

    public function desserts()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Postres',
                'description' => 'Deliciosos postres para finalizar la comida',
                'visible_in_menu' => true,
                'display_order' => 4,
            ];
        });
    }

    public function ingredients()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Ingredientes',
                'description' => 'Ingredientes y materias primas para la cocina',
                'visible_in_menu' => false,
                'display_order' => 100,
            ];
        });
    }

    private function generateCategoryName()
    {
        $categories = [
            'Bebidas', 'Platos Principales', 'Entradas', 'Postres', 'Sopas',
            'Ensaladas', 'Carnes', 'Pescados y Mariscos', 'Pastas', 'Pizzas',
            'Sandwiches', 'Comida Criolla', 'Comida China', 'Comida Italiana',
            'Ingredientes', 'Lácteos', 'Carnes y Aves', 'Verduras', 'Condimentos',
            'Bebidas Calientes', 'Bebidas Frías', 'Jugos Naturales', 'Cervezas',
            'Vinos', 'Licores', 'Snacks', 'Acompañamientos'
        ];

        return $this->faker->randomElement($categories);
    }

    private function generateCategoryDescription()
    {
        $descriptions = [
            'Categoría de productos para el restaurante',
            'Selección especial de productos de calidad',
            'Productos frescos y de temporada',
            'Especialidades de la casa',
            'Productos tradicionales peruanos',
            'Ingredientes de primera calidad',
            'Bebidas refrescantes y nutritivas',
            'Platos preparados con recetas tradicionales',
            'Productos importados y nacionales',
            'Selección gourmet para paladares exigentes'
        ];

        return $this->faker->randomElement($descriptions);
    }
}
