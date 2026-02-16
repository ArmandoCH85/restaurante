<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Order;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => null, // Nullable para facilitar testing
            'cash_register_id' => null,
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'yape', 'plin']),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'reference_number' => null,
            'payment_datetime' => now(),
            'received_by' => null,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'card',
        ]);
    }

    public function yape(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'yape',
        ]);
    }

    public function plin(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'plin',
        ]);
    }

    public function voided(string $reason = 'Anulado'): static
    {
        return $this->state(fn (array $attributes) => [
            'void_reason' => $reason,
            'voided_at' => now(),
        ]);
    }
}
