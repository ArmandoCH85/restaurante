<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $productType = $this->faker->randomElement([
            Product::TYPE_INGREDIENT,
            Product::TYPE_SALE_ITEM,
            Product::TYPE_BOTH
        ]);

        $salePrice = $productType === Product::TYPE_INGREDIENT ? 0 : $this->faker->randomFloat(2, 8, 120);
        $currentCost = $salePrice > 0 ? round($salePrice * $this->faker->randomFloat(2, 0.4, 0.7), 2) : $this->faker->randomFloat(2, 2, 25);

        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{4}'),
            'name' => $this->generateProductName($productType),
            'description' => $this->generateDescription($productType),
            'sale_price' => $salePrice,
            'current_cost' => $currentCost,
            'current_stock' => $this->faker->randomFloat(3, 0, 500),
            'product_type' => $productType,
            'category_id' => ProductCategory::factory(),
            'active' => $this->faker->boolean(90),
            'has_recipe' => $this->shouldHaveRecipe($productType),
            'image_path' => null,
            'available' => $this->faker->boolean(85),
        ];
    }

    public function ingredient()
    {
        return $this->state(function (array $attributes) {
            return [
                'product_type' => Product::TYPE_INGREDIENT,
                'name' => $this->generateIngredientName(),
                'description' => 'Ingrediente para preparación de platos',
                'sale_price' => 0,
                'current_cost' => $this->faker->randomFloat(2, 1, 15),
                'has_recipe' => false,
                'current_stock' => $this->faker->randomFloat(3, 10, 1000),
            ];
        });
    }

    public function saleItem()
    {
        return $this->state(function (array $attributes) {
            $salePrice = $this->faker->randomFloat(2, 12, 85);
            return [
                'product_type' => Product::TYPE_SALE_ITEM,
                'name' => $this->generateDishName(),
                'description' => $this->generateDishDescription(),
                'sale_price' => $salePrice,
                'current_cost' => round($salePrice * $this->faker->randomFloat(2, 0.35, 0.65), 2),
                'has_recipe' => $this->faker->boolean(80),
                'current_stock' => 0, // Sale items typically don't have stock
            ];
        });
    }

    public function both()
    {
        return $this->state(function (array $attributes) {
            $salePrice = $this->faker->randomFloat(2, 8, 45);
            return [
                'product_type' => Product::TYPE_BOTH,
                'name' => $this->generateBothTypeName(),
                'description' => 'Producto que puede usarse como ingrediente o venderse directamente',
                'sale_price' => $salePrice,
                'current_cost' => round($salePrice * $this->faker->randomFloat(2, 0.4, 0.6), 2),
                'has_recipe' => $this->faker->boolean(30),
                'current_stock' => $this->faker->randomFloat(3, 5, 200),
            ];
        });
    }

    public function beverage()
    {
        return $this->state(function (array $attributes) {
            $salePrice = $this->faker->randomFloat(2, 3, 12);
            return [
                'product_type' => Product::TYPE_SALE_ITEM,
                'name' => $this->faker->randomElement(['Inka Cola', 'Coca Cola', 'Pepsi', 'Sprite', 'Chicha Morada', 'Limonada', 'Agua Mineral']),
                'description' => 'Bebida refrescante',
                'sale_price' => $salePrice,
                'current_cost' => round($salePrice * 0.5, 2),
                'has_recipe' => false,
                'current_stock' => 0,
            ];
        });
    }

    public function food()
    {
        return $this->state(function (array $attributes) {
            $salePrice = $this->faker->randomFloat(2, 15, 45);
            return [
                'product_type' => Product::TYPE_SALE_ITEM,
                'name' => $this->faker->randomElement(['Pollo a la Brasa', 'Anticuchos', 'Salchipapas', 'Hamburguesa', 'Lomo Saltado', 'Ají de Gallina']),
                'description' => $this->generateDishDescription(),
                'sale_price' => $salePrice,
                'current_cost' => round($salePrice * $this->faker->randomFloat(2, 0.4, 0.6), 2),
                'has_recipe' => true,
                'current_stock' => 0,
            ];
        });
    }

    public function withRecipe()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_recipe' => true,
            ];
        });
    }

    public function withoutRecipe()
    {
        return $this->state(function (array $attributes) {
            return [
                'has_recipe' => false,
            ];
        });
    }

    private function generateProductName($type)
    {
        switch ($type) {
            case Product::TYPE_INGREDIENT:
                return $this->generateIngredientName();
            case Product::TYPE_SALE_ITEM:
                return $this->generateDishName();
            case Product::TYPE_BOTH:
                return $this->generateBothTypeName();
            default:
                return $this->faker->words(2, true);
        }
    }

    private function generateIngredientName()
    {
        $ingredients = [
            'Pollo', 'Carne de Res', 'Pescado', 'Camarones', 'Arroz', 'Frijoles',
            'Tomate', 'Cebolla', 'Ajo', 'Cilantro', 'Limón', 'Aceite de Oliva',
            'Sal', 'Pimienta', 'Comino', 'Orégano', 'Queso', 'Leche',
            'Huevos', 'Harina', 'Azúcar', 'Mantequilla', 'Papa', 'Zanahoria',
            'Apio', 'Pimiento', 'Choclo', 'Yuca', 'Plátano', 'Aguacate'
        ];

        return $this->faker->randomElement($ingredients);
    }

    private function generateDishName()
    {
        $dishes = [
            'Lomo Saltado', 'Ají de Gallina', 'Arroz con Pollo', 'Ceviche de Pescado',
            'Anticuchos', 'Papa a la Huancaína', 'Causa Limeña', 'Tacu Tacu',
            'Seco de Cabrito', 'Arroz Chaufa', 'Tallarín Saltado', 'Pollo a la Brasa',
            'Chicharrón de Pollo', 'Pescado a lo Macho', 'Sudado de Pescado',
            'Estofado de Pollo', 'Milanesa de Pollo', 'Churrasco', 'Bistec a lo Pobre',
            'Hamburguesa Clásica', 'Pizza Margherita', 'Ensalada César', 'Sopa Criolla'
        ];

        return $this->faker->randomElement($dishes);
    }

    private function generateBothTypeName()
    {
        $bothTypes = [
            'Salsa Criolla', 'Salsa Huancaína', 'Salsa de Ají', 'Pan Francés',
            'Queso Fresco', 'Yogurt Natural', 'Mermelada de Fresa', 'Miel de Abeja',
            'Aceitunas', 'Palta', 'Choclo Desgranado', 'Frijoles Cocidos'
        ];

        return $this->faker->randomElement($bothTypes);
    }

    private function generateDescription($type)
    {
        switch ($type) {
            case Product::TYPE_INGREDIENT:
                return 'Ingrediente fresco para la preparación de diversos platos de la carta';
            case Product::TYPE_SALE_ITEM:
                return $this->generateDishDescription();
            case Product::TYPE_BOTH:
                return 'Producto versátil que puede usarse como ingrediente o venderse directamente';
            default:
                return $this->faker->sentence();
        }
    }

    private function generateDishDescription()
    {
        $descriptions = [
            'Delicioso plato tradicional peruano preparado con ingredientes frescos',
            'Especialidad de la casa con sabores únicos y presentación exquisita',
            'Plato típico con receta familiar transmitida por generaciones',
            'Combinación perfecta de sabores que deleitará tu paladar',
            'Preparado con ingredientes de primera calidad y técnicas culinarias tradicionales',
            'Plato emblemático de nuestra gastronomía con un toque especial',
            'Exquisita preparación que resalta los sabores auténticos',
            'Dish especial recomendado por nuestros chefs'
        ];

        return $this->faker->randomElement($descriptions);
    }

    private function shouldHaveRecipe($type)
    {
        switch ($type) {
            case Product::TYPE_INGREDIENT:
                return false; // Ingredients don't have recipes
            case Product::TYPE_SALE_ITEM:
                return $this->faker->boolean(85); // 85% of sale items have recipes
            case Product::TYPE_BOTH:
                return $this->faker->boolean(40); // 40% of both types have recipes
            default:
                return false;
        }
    }
}
