<?php

namespace Database\Seeders;

use App\Models\Floor;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear pisos predeterminados
        $floors = [
            [
                'name' => 'Primer Piso',
                'description' => 'Área principal del restaurante',
                'status' => 'active',
            ],
            [
                'name' => 'Segundo Piso',
                'description' => 'Área para eventos y grupos grandes',
                'status' => 'active',
            ],
            [
                'name' => 'Terraza',
                'description' => 'Área al aire libre',
                'status' => 'active',
            ],
        ];

        foreach ($floors as $floor) {
            Floor::create($floor);
        }
    }
}
