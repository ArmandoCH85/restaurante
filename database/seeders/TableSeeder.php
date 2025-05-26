<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\Floor;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floors = Floor::all();

        if ($floors->isEmpty()) {
            $this->command->warn('No floors found. Please run FloorSeeder first.');
            return;
        }

        // Primer Piso - Mesas 1-15
        $firstFloor = $floors->where('name', 'Primer Piso')->first();
        if ($firstFloor) {
            for ($i = 1; $i <= 15; $i++) {
                Table::firstOrCreate(
                    ['number' => $i],
                    [
                        'floor_id' => $firstFloor->id,
                        'number' => $i,
                        'shape' => $i % 2 == 0 ? Table::SHAPE_ROUND : Table::SHAPE_SQUARE,
                        'capacity' => rand(2, 6),
                        'location' => 'interior',
                        'status' => Table::STATUS_AVAILABLE,
                    ]
                );
            }
        }

        // Segundo Piso - Mesas 16-25
        $secondFloor = $floors->where('name', 'Segundo Piso')->first();
        if ($secondFloor) {
            for ($i = 16; $i <= 25; $i++) {
                Table::firstOrCreate(
                    ['number' => $i],
                    [
                        'floor_id' => $secondFloor->id,
                        'number' => $i,
                        'shape' => Table::SHAPE_SQUARE,
                        'capacity' => rand(4, 10),
                        'location' => 'private',
                        'status' => Table::STATUS_AVAILABLE,
                    ]
                );
            }
        }

        // Terraza - Mesas 26-35
        $terrace = $floors->where('name', 'Terraza')->first();
        if ($terrace) {
            for ($i = 26; $i <= 35; $i++) {
                Table::firstOrCreate(
                    ['number' => $i],
                    [
                        'floor_id' => $terrace->id,
                        'number' => $i,
                        'shape' => Table::SHAPE_ROUND,
                        'capacity' => rand(2, 8),
                        'location' => 'exterior',
                        'status' => Table::STATUS_AVAILABLE,
                    ]
                );
            }
        }

        $this->command->info('Tables seeded successfully!');
    }
}
