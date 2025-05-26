<?php

namespace Database\Factories;

use App\Models\RecipeDetail;
use App\Models\Recipe;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecipeDetailFactory extends Factory
{
    protected $model = RecipeDetail::class;

    public function definition()
    {
        return [
            'recipe_id' => Recipe::factory(),
            'ingredient_id' => Product::factory()->ingredient(),
            'quantity' => $this->faker->randomFloat(3, 0.1, 5),
            'unit_of_measure' => $this->getRandomUnit(),
        ];
    }

    public function forMeatDish()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Carne de Res', 'quantity' => [0.5, 1.5], 'unit' => 'KG'],
                ['name' => 'Cebolla', 'quantity' => [0.2, 0.5], 'unit' => 'KG'],
                ['name' => 'Tomate', 'quantity' => [0.3, 0.6], 'unit' => 'KG'],
                ['name' => 'Ajo', 'quantity' => [0.02, 0.05], 'unit' => 'KG'],
                ['name' => 'Aceite Vegetal', 'quantity' => [0.05, 0.1], 'unit' => 'LT'],
                ['name' => 'Sal', 'quantity' => [5, 15], 'unit' => 'GR'],
                ['name' => 'Pimienta', 'quantity' => [2, 8], 'unit' => 'GR'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function forChickenDish()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Pollo', 'quantity' => [0.8, 2], 'unit' => 'KG'],
                ['name' => 'Arroz', 'quantity' => [0.3, 0.8], 'unit' => 'KG'],
                ['name' => 'Cebolla', 'quantity' => [0.1, 0.3], 'unit' => 'KG'],
                ['name' => 'Ajo', 'quantity' => [0.01, 0.03], 'unit' => 'KG'],
                ['name' => 'Cilantro', 'quantity' => [0.05, 0.1], 'unit' => 'KG'],
                ['name' => 'Aceite de Oliva', 'quantity' => [0.03, 0.08], 'unit' => 'LT'],
                ['name' => 'Comino', 'quantity' => [2, 5], 'unit' => 'GR'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function forSeafoodDish()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Pescado', 'quantity' => [0.5, 1.2], 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => [5, 15], 'unit' => 'UND'],
                ['name' => 'Cebolla', 'quantity' => [0.2, 0.4], 'unit' => 'KG'],
                ['name' => 'Ají', 'quantity' => [0.02, 0.05], 'unit' => 'KG'],
                ['name' => 'Cilantro', 'quantity' => [0.03, 0.08], 'unit' => 'KG'],
                ['name' => 'Sal', 'quantity' => [3, 10], 'unit' => 'GR'],
                ['name' => 'Camote', 'quantity' => [0.2, 0.5], 'unit' => 'KG'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function forVegetarianDish()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Quinua', 'quantity' => [0.2, 0.5], 'unit' => 'KG'],
                ['name' => 'Verduras Mixtas', 'quantity' => [0.3, 0.8], 'unit' => 'KG'],
                ['name' => 'Queso', 'quantity' => [0.1, 0.3], 'unit' => 'KG'],
                ['name' => 'Aceite de Oliva', 'quantity' => [0.02, 0.05], 'unit' => 'LT'],
                ['name' => 'Orégano', 'quantity' => [2, 5], 'unit' => 'GR'],
                ['name' => 'Sal', 'quantity' => [3, 8], 'unit' => 'GR'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function forDessert()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Harina', 'quantity' => [0.2, 0.5], 'unit' => 'KG'],
                ['name' => 'Azúcar', 'quantity' => [0.1, 0.3], 'unit' => 'KG'],
                ['name' => 'Huevos', 'quantity' => [2, 6], 'unit' => 'UND'],
                ['name' => 'Mantequilla', 'quantity' => [0.05, 0.15], 'unit' => 'KG'],
                ['name' => 'Leche', 'quantity' => [0.1, 0.3], 'unit' => 'LT'],
                ['name' => 'Vainilla', 'quantity' => [2, 5], 'unit' => 'ML'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function forBeverage()
    {
        return $this->state(function (array $attributes) {
            $ingredients = [
                ['name' => 'Agua', 'quantity' => [0.5, 2], 'unit' => 'LT'],
                ['name' => 'Azúcar', 'quantity' => [0.05, 0.2], 'unit' => 'KG'],
                ['name' => 'Limón', 'quantity' => [2, 8], 'unit' => 'UND'],
                ['name' => 'Hielo', 'quantity' => [0.1, 0.3], 'unit' => 'KG'],
                ['name' => 'Menta', 'quantity' => [5, 15], 'unit' => 'GR'],
            ];

            $ingredient = $this->faker->randomElement($ingredients);
            return [
                'ingredient_id' => Product::factory()->ingredient()->state([
                    'name' => $ingredient['name']
                ]),
                'quantity' => $this->faker->randomFloat(3, $ingredient['quantity'][0], $ingredient['quantity'][1]),
                'unit_of_measure' => $ingredient['unit'],
            ];
        });
    }

    public function smallQuantity()
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->randomFloat(3, 0.01, 0.5),
                'unit_of_measure' => $this->faker->randomElement(['GR', 'ML', 'UND']),
            ];
        });
    }

    public function largeQuantity()
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->randomFloat(3, 1, 10),
                'unit_of_measure' => $this->faker->randomElement(['KG', 'LT']),
            ];
        });
    }

    private function getRandomUnit()
    {
        $units = ['KG', 'GR', 'LT', 'ML', 'UND', 'TZA', 'CDT'];
        return $this->faker->randomElement($units);
    }
}
