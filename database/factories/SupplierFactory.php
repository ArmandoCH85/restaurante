<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition()
    {
        return [
            'business_name' => $this->generateBusinessName(),
            'tax_id' => $this->faker->unique()->numerify('20#########'),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
            'active' => $this->faker->boolean(90),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
            ];
        });
    }

    public function meatSupplier()
    {
        return $this->state(function (array $attributes) {
            return [
                'business_name' => $this->faker->randomElement([
                    'Carnes San Juan S.A.C.',
                    'Distribuidora de Carnes Lima',
                    'Frigorífico Central',
                    'Carnes Premium del Perú',
                    'Distribuidora Ganadera Norte'
                ]),
                'contact_name' => $this->faker->name,
            ];
        });
    }

    public function vegetableSupplier()
    {
        return $this->state(function (array $attributes) {
            return [
                'business_name' => $this->faker->randomElement([
                    'Verduras Frescas del Valle',
                    'Mercado Central de Abastos',
                    'Distribuidora Agrícola Lima',
                    'Productos del Campo S.A.',
                    'Verduras Orgánicas del Sur'
                ]),
                'contact_name' => $this->faker->name,
            ];
        });
    }

    public function beverageSupplier()
    {
        return $this->state(function (array $attributes) {
            return [
                'business_name' => $this->faker->randomElement([
                    'Distribuidora de Bebidas Lima',
                    'Coca Cola del Perú',
                    'Backus y Johnston',
                    'Distribuidora San Miguel',
                    'Bebidas Refrescantes S.A.'
                ]),
                'contact_name' => $this->faker->name,
            ];
        });
    }

    public function dairySupplier()
    {
        return $this->state(function (array $attributes) {
            return [
                'business_name' => $this->faker->randomElement([
                    'Lácteos Gloria S.A.',
                    'Laive S.A.',
                    'Distribuidora de Lácteos Norte',
                    'Productos Lácteos Andinos',
                    'Quesos y Derivados del Perú'
                ]),
                'contact_name' => $this->faker->name,
            ];
        });
    }

    public function seafoodSupplier()
    {
        return $this->state(function (array $attributes) {
            return [
                'business_name' => $this->faker->randomElement([
                    'Pescados y Mariscos del Callao',
                    'Distribuidora Marina Lima',
                    'Productos del Mar S.A.C.',
                    'Mariscos Frescos del Norte',
                    'Pesquera San José'
                ]),
                'contact_name' => $this->faker->name,
            ];
        });
    }

    private function generateBusinessName()
    {
        $types = [
            'Distribuidora', 'Comercializadora', 'Importadora', 'Exportadora',
            'Productos', 'Alimentos', 'Servicios', 'Empresa', 'Corporación'
        ];

        $products = [
            'Alimentarios', 'del Perú', 'Lima', 'Andinos', 'del Norte',
            'del Sur', 'Centrales', 'Premium', 'Selectos', 'Frescos',
            'Naturales', 'Orgánicos', 'Gourmet', 'Tradicionales'
        ];

        $suffixes = ['S.A.', 'S.A.C.', 'E.I.R.L.', 'S.R.L.'];

        $type = $this->faker->randomElement($types);
        $product = $this->faker->randomElement($products);
        $suffix = $this->faker->randomElement($suffixes);

        return "{$type} {$product} {$suffix}";
    }
}
