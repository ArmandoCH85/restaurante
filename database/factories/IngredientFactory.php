<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition()
    {
        $name = $this->generateIngredientName();
        $unitOfMeasure = $this->getUnitOfMeasure($name);
        $currentCost = $this->getCostByType($name);
        $minStock = $this->getMinStockByType($name);
        $currentStock = $this->faker->randomFloat(3, $minStock, $minStock * 5);

        return [
            'name' => $name,
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'description' => $this->generateDescription($name),
            'unit_of_measure' => $unitOfMeasure,
            'min_stock' => $minStock,
            'current_stock' => $currentStock,
            'current_cost' => $currentCost,
            'supplier_id' => Supplier::factory(),
            'active' => $this->faker->boolean(95),
        ];
    }

    public function meat()
    {
        return $this->state(function (array $attributes) {
            $meats = ['Pollo', 'Carne de Res', 'Cerdo', 'Cordero', 'Pavo'];
            return [
                'name' => $this->faker->randomElement($meats),
                'unit_of_measure' => 'KG',
                'min_stock' => $this->faker->randomFloat(3, 5, 20),
                'current_cost' => $this->faker->randomFloat(2, 12, 35),
                'supplier_id' => Supplier::factory()->meatSupplier(),
            ];
        });
    }

    public function vegetable()
    {
        return $this->state(function (array $attributes) {
            $vegetables = ['Tomate', 'Cebolla', 'Papa', 'Zanahoria', 'Apio', 'Pimiento', 'Lechuga', 'Brócoli'];
            return [
                'name' => $this->faker->randomElement($vegetables),
                'unit_of_measure' => 'KG',
                'min_stock' => $this->faker->randomFloat(3, 2, 10),
                'current_cost' => $this->faker->randomFloat(2, 1.5, 8),
                'supplier_id' => Supplier::factory()->vegetableSupplier(),
            ];
        });
    }

    public function seafood()
    {
        return $this->state(function (array $attributes) {
            $seafood = ['Pescado', 'Camarones', 'Langostinos', 'Pulpo', 'Calamar', 'Mejillones'];
            return [
                'name' => $this->faker->randomElement($seafood),
                'unit_of_measure' => 'KG',
                'min_stock' => $this->faker->randomFloat(3, 3, 15),
                'current_cost' => $this->faker->randomFloat(2, 15, 45),
                'supplier_id' => Supplier::factory()->seafoodSupplier(),
            ];
        });
    }

    public function dairy()
    {
        return $this->state(function (array $attributes) {
            $dairy = ['Leche', 'Queso', 'Mantequilla', 'Yogurt', 'Crema de Leche'];
            $name = $this->faker->randomElement($dairy);
            return [
                'name' => $name,
                'unit_of_measure' => $name === 'Leche' ? 'LT' : 'KG',
                'min_stock' => $this->faker->randomFloat(3, 1, 5),
                'current_cost' => $this->faker->randomFloat(2, 3, 15),
                'supplier_id' => Supplier::factory()->dairySupplier(),
            ];
        });
    }

    public function spice()
    {
        return $this->state(function (array $attributes) {
            $spices = ['Sal', 'Pimienta', 'Comino', 'Orégano', 'Ajo en Polvo', 'Paprika', 'Canela'];
            return [
                'name' => $this->faker->randomElement($spices),
                'unit_of_measure' => 'GR',
                'min_stock' => $this->faker->randomFloat(3, 100, 500),
                'current_cost' => $this->faker->randomFloat(2, 0.5, 5),
            ];
        });
    }

    public function grain()
    {
        return $this->state(function (array $attributes) {
            $grains = ['Arroz', 'Frijoles', 'Lentejas', 'Quinua', 'Avena', 'Harina'];
            return [
                'name' => $this->faker->randomElement($grains),
                'unit_of_measure' => 'KG',
                'min_stock' => $this->faker->randomFloat(3, 10, 50),
                'current_cost' => $this->faker->randomFloat(2, 2, 12),
            ];
        });
    }

    public function oil()
    {
        return $this->state(function (array $attributes) {
            $oils = ['Aceite de Oliva', 'Aceite Vegetal', 'Aceite de Girasol', 'Vinagre'];
            return [
                'name' => $this->faker->randomElement($oils),
                'unit_of_measure' => 'LT',
                'min_stock' => $this->faker->randomFloat(3, 2, 10),
                'current_cost' => $this->faker->randomFloat(2, 8, 25),
            ];
        });
    }

    private function generateIngredientName()
    {
        $ingredients = [
            // Carnes
            'Pollo', 'Carne de Res', 'Cerdo', 'Pescado', 'Camarones',
            // Verduras
            'Tomate', 'Cebolla', 'Ajo', 'Papa', 'Zanahoria', 'Apio', 'Pimiento',
            'Lechuga', 'Cilantro', 'Perejil', 'Brócoli', 'Coliflor',
            // Lácteos
            'Leche', 'Queso', 'Mantequilla', 'Yogurt', 'Crema de Leche',
            // Granos y cereales
            'Arroz', 'Frijoles', 'Lentejas', 'Quinua', 'Avena', 'Harina',
            // Condimentos
            'Sal', 'Pimienta', 'Comino', 'Orégano', 'Ajo en Polvo', 'Paprika',
            // Aceites
            'Aceite de Oliva', 'Aceite Vegetal', 'Vinagre',
            // Otros
            'Huevos', 'Azúcar', 'Limón', 'Choclo', 'Yuca', 'Plátano'
        ];

        return $this->faker->randomElement($ingredients);
    }

    private function getUnitOfMeasure($name)
    {
        $units = [
            'Pollo' => 'KG', 'Carne de Res' => 'KG', 'Cerdo' => 'KG', 'Pescado' => 'KG',
            'Camarones' => 'KG', 'Tomate' => 'KG', 'Cebolla' => 'KG', 'Papa' => 'KG',
            'Zanahoria' => 'KG', 'Apio' => 'KG', 'Pimiento' => 'KG', 'Lechuga' => 'UND',
            'Leche' => 'LT', 'Queso' => 'KG', 'Mantequilla' => 'KG', 'Yogurt' => 'LT',
            'Arroz' => 'KG', 'Frijoles' => 'KG', 'Harina' => 'KG', 'Aceite de Oliva' => 'LT',
            'Aceite Vegetal' => 'LT', 'Sal' => 'GR', 'Pimienta' => 'GR', 'Comino' => 'GR',
            'Huevos' => 'UND', 'Azúcar' => 'KG', 'Limón' => 'UND'
        ];

        return $units[$name] ?? 'UND';
    }

    private function getCostByType($name)
    {
        $costs = [
            'Pollo' => [15, 25], 'Carne de Res' => [25, 40], 'Pescado' => [20, 35],
            'Camarones' => [35, 55], 'Tomate' => [2, 5], 'Cebolla' => [1.5, 3],
            'Papa' => [1, 2.5], 'Leche' => [3, 5], 'Queso' => [12, 20],
            'Arroz' => [3, 6], 'Aceite de Oliva' => [15, 25], 'Sal' => [0.5, 2],
            'Huevos' => [0.3, 0.6], 'Azúcar' => [2, 4]
        ];

        $range = $costs[$name] ?? [1, 10];
        return $this->faker->randomFloat(2, $range[0], $range[1]);
    }

    private function getMinStockByType($name)
    {
        $stocks = [
            'Pollo' => [5, 15], 'Carne de Res' => [5, 15], 'Pescado' => [3, 10],
            'Tomate' => [5, 20], 'Cebolla' => [5, 20], 'Papa' => [10, 30],
            'Leche' => [5, 15], 'Queso' => [2, 8], 'Arroz' => [20, 50],
            'Aceite de Oliva' => [2, 8], 'Sal' => [500, 2000], 'Huevos' => [50, 200]
        ];

        $range = $stocks[$name] ?? [1, 10];
        return $this->faker->randomFloat(3, $range[0], $range[1]);
    }

    private function generateDescription($name)
    {
        $descriptions = [
            'Ingrediente fresco de primera calidad',
            'Producto seleccionado para la cocina',
            'Ingrediente esencial para nuestras recetas',
            'Materia prima de origen nacional',
            'Producto importado de alta calidad',
            'Ingrediente orgánico certificado',
            'Producto fresco del día',
            'Ingrediente tradicional peruano'
        ];

        return $this->faker->randomElement($descriptions) . " - {$name}";
    }
}
