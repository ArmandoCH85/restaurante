<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FactilizaTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe el registro
        $existing = DB::table('app_settings')
            ->where('tab', 'Empresa')
            ->where('key', 'factiliza_token')
            ->first();

        // Si no existe, crearlo
        if (!$existing) {
            DB::table('app_settings')->insert([
                'id' => Str::uuid()->toString(),
                'tab' => 'Empresa',
                'key' => 'factiliza_token',
                'default' => '',
                'value' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('Registro factiliza_token creado correctamente.');
        } else {
            $this->command->info('El registro factiliza_token ya existe.');
        }
    }
}