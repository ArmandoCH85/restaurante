<?php

namespace Database\Factories;

use App\Models\CashRegisterExpense;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterExpenseFactory extends Factory
{
    protected $model = CashRegisterExpense::class;

    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 200),
            'concept' => $this->faker->sentence(),
            'notes' => null,
        ];
    }
}
