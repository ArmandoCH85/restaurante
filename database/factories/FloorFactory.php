<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Primer Piso',
                'Segundo Piso', 
                'Terraza',
                'Planta Baja',
                'Área VIP',
                'Salón Principal'
            ]),
            'description' => $this->faker->randomElement([
                'Área principal del restaurante',
                'Zona para eventos y grupos grandes',
                'Área al aire libre con vista panorámica',
                'Espacio acogedor para familias',
                'Zona exclusiva para clientes VIP',
                'Salón amplio y cómodo'
            ]),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'maintenance']), // 75% active
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    public function maintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'maintenance',
            ];
        });
    }

    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'closed',
            ];
        });
    }
}
