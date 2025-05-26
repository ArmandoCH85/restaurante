<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'invoice_type' => $this->faker->randomElement(['invoice', 'receipt', 'sales_note']),
            'series' => $this->faker->randomElement(['F001', 'B001', 'NV001']),
            'number' => str_pad($this->faker->numberBetween(1, 9999), 8, '0', STR_PAD_LEFT),
            'issue_date' => $this->faker->date(),
            'customer_id' => Customer::factory(),
            'taxable_amount' => $this->faker->randomFloat(2, 10, 100),
            'tax' => function (array $attributes) {
                return round($attributes['taxable_amount'] * 0.18, 2);
            },
            'total' => function (array $attributes) {
                return $attributes['taxable_amount'] + $attributes['tax'];
            },
            'tax_authority_status' => 'pending',
            'codigo_tipo_moneda' => 'PEN',
            'codigo_tipo_operacion' => '0101',
        ];
    }

    public function invoice()
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_type' => 'invoice',
                'series' => 'F001',
            ];
        });
    }

    public function receipt()
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_type' => 'receipt',
                'series' => 'B001',
            ];
        });
    }

    public function salesNote()
    {
        return $this->state(function (array $attributes) {
            return [
                'invoice_type' => 'sales_note',
                'series' => 'NV001',
            ];
        });
    }

    public function withCustomer(Customer $customer)
    {
        return $this->state(function (array $attributes) use ($customer) {
            return [
                'customer_id' => $customer->id,
            ];
        });
    }
}
