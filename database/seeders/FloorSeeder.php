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

        foreach ($floors as $floorData) {
            Floor::firstOrCreate(
                ['name' => $floorData['name']],
                $floorData
            );
        }

        $this->command->info('Floors seeded successfully!');
    }
}
