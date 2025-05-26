<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition()
    {
        return [
            'name' => $this->generateWarehouseName(),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{2}'),
            'description' => $this->generateDescription(),
            'location' => $this->generateLocation(),
            'is_default' => false,
            'active' => $this->faker->boolean(90),
            'created_by' => User::factory(),
        ];
    }

    public function default()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Almacén Principal',
                'code' => 'ALM01',
                'description' => 'Almacén principal del restaurante para ingredientes y productos',
                'location' => 'Planta Baja - Área de Cocina',
                'is_default' => true,
                'active' => true,
            ];
        });
    }

    public function kitchen()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Almacén de Cocina',
                'code' => 'COC01',
                'description' => 'Almacén ubicado en la cocina para ingredientes de uso diario',
                'location' => 'Cocina Principal',
                'is_default' => false,
                'active' => true,
            ];
        });
    }

    public function beverages()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Almacén de Bebidas',
                'code' => 'BEB01',
                'description' => 'Almacén especializado para bebidas y líquidos',
                'location' => 'Área de Bar',
                'is_default' => false,
                'active' => true,
            ];
        });
    }

    public function dryGoods()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Almacén de Secos',
                'code' => 'SEC01',
                'description' => 'Almacén para productos secos y no perecederos',
                'location' => 'Depósito General',
                'is_default' => false,
                'active' => true,
            ];
        });
    }

    public function freezer()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Cámara Frigorífica',
                'code' => 'FRZ01',
                'description' => 'Cámara frigorífica para productos congelados',
                'location' => 'Área de Refrigeración',
                'is_default' => false,
                'active' => true,
            ];
        });
    }

    public function refrigerator()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Refrigerador Principal',
                'code' => 'REF01',
                'description' => 'Refrigerador para productos frescos y perecederos',
                'location' => 'Cocina - Área de Refrigeración',
                'is_default' => false,
                'active' => true,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => false,
                'description' => $attributes['description'] . ' (Inactivo)',
            ];
        });
    }

    private function generateWarehouseName()
    {
        $types = [
            'Almacén', 'Depósito', 'Bodega', 'Cámara', 'Área de Almacenamiento'
        ];

        $locations = [
            'Principal', 'Secundario', 'de Cocina', 'de Bebidas', 'de Secos',
            'de Refrigerados', 'de Congelados', 'General', 'Temporal', 'de Emergencia'
        ];

        $type = $this->faker->randomElement($types);
        $location = $this->faker->randomElement($locations);

        return "{$type} {$location}";
    }

    private function generateDescription()
    {
        $descriptions = [
            'Espacio de almacenamiento para productos del restaurante',
            'Área designada para el control de inventarios',
            'Almacén con control de temperatura y humedad',
            'Depósito organizado para fácil acceso a productos',
            'Área de almacenamiento con sistema de rotación FIFO',
            'Espacio climatizado para conservación de productos',
            'Almacén con sistema de seguridad y control de acceso',
            'Área de almacenamiento temporal para recepciones',
            'Depósito especializado para diferentes tipos de productos',
            'Almacén con capacidad para productos de gran volumen'
        ];

        return $this->faker->randomElement($descriptions);
    }

    private function generateLocation()
    {
        $areas = [
            'Planta Baja', 'Primer Piso', 'Sótano', 'Área de Cocina',
            'Área de Bar', 'Depósito General', 'Zona de Carga y Descarga',
            'Área de Refrigeración', 'Pasillo Principal', 'Área de Servicio'
        ];

        $details = [
            'Lado Derecho', 'Lado Izquierdo', 'Área Central', 'Esquina Norte',
            'Esquina Sur', 'Junto a la Cocina', 'Cerca del Comedor',
            'Área Posterior', 'Zona de Acceso', 'Área Restringida'
        ];

        $area = $this->faker->randomElement($areas);
        $detail = $this->faker->randomElement($details);

        return "{$area} - {$detail}";
    }
}
