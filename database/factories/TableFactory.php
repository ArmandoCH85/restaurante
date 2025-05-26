<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        return [
            'floor_id' => Floor::factory(),
            'number' => $this->faker->unique()->numberBetween(1, 50),
            'shape' => $this->faker->randomElement([Table::SHAPE_SQUARE, Table::SHAPE_ROUND]),
            'capacity' => $this->faker->numberBetween(2, 8),
            'location' => $this->faker->randomElement(['interior', 'exterior', 'bar', 'private']),
            'status' => $this->faker->randomElement([
                Table::STATUS_AVAILABLE,
                Table::STATUS_AVAILABLE,
                Table::STATUS_AVAILABLE,
                Table::STATUS_OCCUPIED,
                Table::STATUS_RESERVED
            ]), // 60% available, 20% occupied, 20% reserved
            'qr_code' => null,
        ];
    }

    public function available()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Table::STATUS_AVAILABLE,
            ];
        });
    }

    public function occupied()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Table::STATUS_OCCUPIED,
            ];
        });
    }

    public function reserved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Table::STATUS_RESERVED,
            ];
        });
    }

    public function maintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Table::STATUS_MAINTENANCE,
            ];
        });
    }

    public function square()
    {
        return $this->state(function (array $attributes) {
            return [
                'shape' => Table::SHAPE_SQUARE,
            ];
        });
    }

    public function round()
    {
        return $this->state(function (array $attributes) {
            return [
                'shape' => Table::SHAPE_ROUND,
            ];
        });
    }

    public function small()
    {
        return $this->state(function (array $attributes) {
            return [
                'capacity' => $this->faker->numberBetween(2, 4),
            ];
        });
    }

    public function large()
    {
        return $this->state(function (array $attributes) {
            return [
                'capacity' => $this->faker->numberBetween(6, 12),
            ];
        });
    }
}
