<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // Ubicaciones para las mesas
        $locations = ['interior', 'terraza', 'vip', 'barra'];

        // Mesas en el interior
        for ($i = 1; $i <= 12; $i++) {
            Table::create([
                'number' => $i,
                'capacity' => rand(2, 6),
                'location' => 'interior',
                'status' => $this->getRandomStatus(),
            ]);
        }

        // Mesas en la terraza
        for ($i = 13; $i <= 20; $i++) {
            Table::create([
                'number' => $i,
                'capacity' => rand(2, 8),
                'location' => 'terraza',
                'status' => $this->getRandomStatus(),
            ]);
        }

        // Mesas VIP
        for ($i = 21; $i <= 24; $i++) {
            Table::create([
                'number' => $i,
                'capacity' => rand(4, 10),
                'location' => 'vip',
                'status' => $this->getRandomStatus(),
            ]);
        }

        // Mesas de barra
        for ($i = 25; $i <= 30; $i++) {
            Table::create([
                'number' => $i,
                'capacity' => 2,
                'location' => 'barra',
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
}
