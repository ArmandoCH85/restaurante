<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'document_type' => $this->faker->randomElement(['DNI', 'RUC', 'CE']),
            'document_number' => $this->faker->numerify('########'),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
        ];
    }

    public function withDNI()
    {
        return $this->state(function (array $attributes) {
            return [
                'document_type' => 'DNI',
                'document_number' => $this->faker->numerify('########'),
                'name' => $this->faker->name(),
            ];
        });
    }

    public function withRUC()
    {
        return $this->state(function (array $attributes) {
            return [
                'document_type' => 'RUC',
                'document_number' => $this->faker->numerify('###########'),
                'name' => $this->faker->company() . ' S.A.C.',
            ];
        });
    }
}
