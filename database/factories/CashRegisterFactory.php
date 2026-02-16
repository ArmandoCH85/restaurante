<?php

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterFactory extends Factory
{
    protected $model = CashRegister::class;

    public function definition(): array
    {
        return [
            'opened_by' => User::factory(),
            'opening_amount' => $this->faker->randomFloat(2, 100, 500),
            'opening_datetime' => now(),
            'is_active' => true,
            'cash_sales' => 0,
            'card_sales' => 0,
            'other_sales' => 0,
            'total_sales' => 0,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'closed_by' => User::factory(),
            'closing_datetime' => now(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'approved_by' => User::factory(),
            'approval_datetime' => now(),
        ]);
    }
}
