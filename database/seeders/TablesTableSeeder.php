<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TablesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si hay relaciones en otras tablas antes de eliminar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Table::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Obtener los pisos disponibles
        $floors = Floor::all();

        if ($floors->isEmpty()) {
            $this->command->info('No hay pisos disponibles. Ejecuta el seeder de pisos primero.');
            return;
        }

        // Primer piso - Mesas interiores (1-12)
        $firstFloor = $floors->where('name', 'Primer Piso')->first();
        for ($i = 1; $i <= 12; $i++) {
            Table::create([
                'floor_id' => $firstFloor->id,
                'number' => $i,
                'shape' => $this->getRandomShape(),
                'capacity' => rand(2, 6),
                'location' => 'interior',
                'status' => $this->getRandomStatus(),
            ]);
        }

        // Segundo piso - Mesas VIP (13-20)
        $secondFloor = $floors->where('name', 'Segundo Piso')->first();
        for ($i = 13; $i <= 20; $i++) {
            Table::create([
                'floor_id' => $secondFloor->id,
                'number' => $i,
                'shape' => $this->getRandomShape(),
                'capacity' => rand(4, 10),
                'location' => 'vip',
                'status' => $this->getRandomStatus(),
            ]);
        }

        // Terraza - Mesas al aire libre (21-30)
        $terrace = $floors->where('name', 'Terraza')->first();
        for ($i = 21; $i <= 30; $i++) {
            Table::create([
                'floor_id' => $terrace->id,
                'number' => $i,
                'shape' => $this->getRandomShape(),
                'capacity' => rand(2, 8),
                'location' => 'terraza',
                'status' => $this->getRandomStatus(),
            ]);
        }
    }

    /**
     * Obtener un estado aleatorio con probabilidades establecidas
     */
    private function getRandomStatus(): string
    {
        $statuses = [
            Table::STATUS_AVAILABLE => 50, // 50% de probabilidad
            Table::STATUS_OCCUPIED => 30,  // 30% de probabilidad
            Table::STATUS_RESERVED => 15,  // 15% de probabilidad
            Table::STATUS_MAINTENANCE => 5 // 5% de probabilidad
        ];

        $randomNumber = rand(1, 100);
        $cumulative = 0;

        foreach ($statuses as $status => $probability) {
            $cumulative += $probability;
            if ($randomNumber <= $cumulative) {
                return $status;
            }
        }

        return Table::STATUS_AVAILABLE;
    }

    /**
     * Obtener una forma aleatoria para la mesa
     */
    private function getRandomShape(): string
    {
        return rand(0, 1) ? Table::SHAPE_SQUARE : Table::SHAPE_ROUND;
    }
}
