<?php

namespace Database\Factories;

use App\Models\IngredientStock;
use App\Models\Ingredient;
use App\Models\Warehouse;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredientStockFactory extends Factory
{
    protected $model = IngredientStock::class;

    public function definition()
    {
        $quantity = $this->faker->randomFloat(3, 1, 100);
        $unitCost = $this->faker->randomFloat(2, 0.5, 50);
        
        return [
            'ingredient_id' => Ingredient::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'expiry_date' => $this->generateExpiryDate(),
            'status' => $this->faker->randomElement([
                IngredientStock::STATUS_AVAILABLE,
                IngredientStock::STATUS_AVAILABLE,
                IngredientStock::STATUS_AVAILABLE,
                IngredientStock::STATUS_RESERVED,
                IngredientStock::STATUS_EXPIRED
            ]), // 75% available, 12.5% reserved, 12.5% expired
            'purchase_id' => Purchase::factory(),
        ];
    }

    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IngredientStock::STATUS_AVAILABLE,
                'expiry_date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            ];
        });
    }

    public function reserved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IngredientStock::STATUS_RESERVED,
                'expiry_date' => $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            ];
        });
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IngredientStock::STATUS_EXPIRED,
                'expiry_date' => $this->faker->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d'),
                'quantity' => 0, // Expired items should have 0 quantity
            ];
        });
    }

    public function fresh()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IngredientStock::STATUS_AVAILABLE,
                'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+12 months')->format('Y-m-d'),
                'quantity' => $this->faker->randomFloat(3, 10, 200),
            ];
        });
    }

    public function nearExpiry()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IngredientStock::STATUS_AVAILABLE,
                'expiry_date' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
                'quantity' => $this->faker->randomFloat(3, 1, 50),
            ];
        });
    }

    public function meat()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->meat(),
                'quantity' => $this->faker->randomFloat(3, 5, 50),
                'unit_cost' => $this->faker->randomFloat(2, 15, 40),
                'expiry_date' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function vegetable()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->vegetable(),
                'quantity' => $this->faker->randomFloat(3, 2, 30),
                'unit_cost' => $this->faker->randomFloat(2, 1, 8),
                'expiry_date' => $this->faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function seafood()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->seafood(),
                'quantity' => $this->faker->randomFloat(3, 3, 25),
                'unit_cost' => $this->faker->randomFloat(2, 20, 60),
                'expiry_date' => $this->faker->dateTimeBetween('now', '+3 days')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function dairy()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->dairy(),
                'quantity' => $this->faker->randomFloat(3, 1, 20),
                'unit_cost' => $this->faker->randomFloat(2, 3, 18),
                'expiry_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function spice()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->spice(),
                'quantity' => $this->faker->randomFloat(3, 100, 2000), // grams
                'unit_cost' => $this->faker->randomFloat(2, 0.5, 8),
                'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function grain()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->grain(),
                'quantity' => $this->faker->randomFloat(3, 10, 100),
                'unit_cost' => $this->faker->randomFloat(2, 2, 15),
                'expiry_date' => $this->faker->dateTimeBetween('+3 months', '+18 months')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function oil()
    {
        return $this->state(function (array $attributes) {
            return [
                'ingredient_id' => Ingredient::factory()->oil(),
                'quantity' => $this->faker->randomFloat(3, 1, 20), // liters
                'unit_cost' => $this->faker->randomFloat(2, 8, 30),
                'expiry_date' => $this->faker->dateTimeBetween('+6 months', '+2 years')->format('Y-m-d'),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function lowStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->randomFloat(3, 0.1, 2),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function highStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'quantity' => $this->faker->randomFloat(3, 50, 500),
                'status' => IngredientStock::STATUS_AVAILABLE,
            ];
        });
    }

    public function fifoOld()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
                'status' => IngredientStock::STATUS_AVAILABLE,
                'expiry_date' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            ];
        });
    }

    public function fifoNew()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'status' => IngredientStock::STATUS_AVAILABLE,
                'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            ];
        });
    }

    private function generateExpiryDate()
    {
        // 70% of items have expiry dates in the future
        // 20% have expiry dates soon (within 2 weeks)
        // 10% are expired
        $random = $this->faker->numberBetween(1, 100);
        
        if ($random <= 10) {
            // Expired
            return $this->faker->dateTimeBetween('-6 months', '-1 day')->format('Y-m-d');
        } elseif ($random <= 30) {
            // Expiring soon
            return $this->faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d');
        } else {
            // Future expiry
            return $this->faker->dateTimeBetween('+2 weeks', '+12 months')->format('Y-m-d');
        }
    }
}
