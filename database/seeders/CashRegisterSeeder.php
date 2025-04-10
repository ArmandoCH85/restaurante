<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CashRegisterSeeder extends Seeder
{
    /**
     * Seed para la tabla de cajas registradoras
     */
    public function run(): void
    {
        // Obtener el primer usuario admin para usarlo como opened_by
        $admin = User::first();

        if (!$admin) {
            $this->command->info('No se encontraron usuarios. Cree al menos un usuario antes de ejecutar este seeder.');
            return;
        }

        // Verificar si ya existe una caja abierta
        $existingOpen = CashRegister::where('is_active', CashRegister::STATUS_OPEN)->first();

        if ($existingOpen) {
            $this->command->info('Ya existe una caja abierta. No se creará otra.');
            return;
        }

        // Crear una caja registradora abierta
        CashRegister::create([
            'opened_by' => $admin->id,
            'opening_amount' => 100.00, // Monto inicial de 100
            'is_active' => CashRegister::STATUS_OPEN,
            'opening_datetime' => now(),
        ]);

        $this->command->info('Se ha creado una caja registradora abierta.');
    }
}
