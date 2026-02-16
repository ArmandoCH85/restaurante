<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Table;
use App\Models\Customer;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 20, 500);
        $tax = $subtotal * 0.18;
        $total = $subtotal + $tax;

        return [
            'service_type' => $this->faker->randomElement(['takeaway', 'delivery', 'drive_thru']),
            'table_id' => null,
            'customer_id' => null,
            'employee_id' => function() {
                return Employee::factory()->create()->id;
            },
            'cash_register_id' => null,
            'order_datetime' => now(),
            'status' => 'completed',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'notes' => null,
            'billed' => false,
            'payment_method' => null,
            'payment_amount' => null,
        ];
    }

    public function dineIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'dine_in',
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'delivery',
            'table_id' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function billed(): static
    {
        return $this->state(fn (array $attributes) => [
            'billed' => true,
        ]);
    }
}
