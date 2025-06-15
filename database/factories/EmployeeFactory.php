<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'document_number' => $this->faker->unique()->numerify('########'),
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'position' => $this->faker->jobTitle,
            'hire_date' => $this->faker->date(),
            'base_salary' => $this->faker->randomFloat(2, 1000, 5000),
        ];
    }
}
