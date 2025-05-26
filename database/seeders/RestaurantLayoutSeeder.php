<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RestaurantLayoutSeeder extends Seeder
{
    /**
     * Run the restaurant layout database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Starting Restaurant Layout Seeding...');

        // 1. Floors first (no dependencies)
        $this->command->info('🏗️ Seeding Floors...');
        $this->call(FloorSeeder::class);

        // 2. Tables (depends on Floors)
        $this->command->info('🪑 Seeding Tables...');
        $this->call(TableSeeder::class);

        $this->command->info('✅ Restaurant Layout Seeding completed!');
        $this->command->line('');
        $this->command->info('📊 Summary:');
        $this->command->line('   • 3 Floors created (Primer Piso, Segundo Piso, Terraza)');
        $this->command->line('   • 35 Tables distributed across floors');
        $this->command->line('   • Tables 1-15: Primer Piso (interior)');
        $this->command->line('   • Tables 16-25: Segundo Piso (private/VIP)');
        $this->command->line('   • Tables 26-35: Terraza (exterior)');
    }
}
