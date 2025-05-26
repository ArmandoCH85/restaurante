<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FixDeliveryEmployee extends Command
{
    protected $signature = 'delivery:fix-employee {employee_id}';
    protected $description = 'Corregir empleado para que funcione como repartidor';

    public function handle()
    {
        $employeeId = $this->argument('employee_id');

        $employee = Employee::find($employeeId);

        if (!$employee) {
            $this->error("Empleado con ID {$employeeId} no encontrado");
            return 1;
        }

        $this->info("🔧 CORRIGIENDO EMPLEADO PARA DELIVERY");
        $this->line("");
        $this->line("👤 Empleado: {$employee->full_name}");
        $this->line("🏷️  Posición actual: '{$employee->position}'");
        $this->line("");

        // 1. Cambiar posición a 'Delivery'
        $this->info("1️⃣ Cambiando posición a 'Delivery'...");
        $employee->position = 'Delivery';
        $employee->save();
        $this->line("   ✅ Posición actualizada");

        // 2. Crear usuario si no existe
        if (!$employee->user) {
            $this->info("2️⃣ Creando usuario para el empleado...");

            $email = $this->ask("Email para el usuario", strtolower(str_replace(' ', '.', $employee->full_name)) . '@restaurante.com');
            $password = $this->secret("Contraseña (dejar vacío para usar 'password')") ?: 'password';

            $user = User::create([
                'name' => $employee->full_name,
                'email' => $email,
                'password' => bcrypt($password),
                'email_verified_at' => now()
            ]);

            $employee->user_id = $user->id;
            $employee->save();

            $this->line("   ✅ Usuario creado: {$email}");
        } else {
            $this->line("2️⃣ Usuario ya existe: {$employee->user->email}");
        }

        // 3. Crear rol 'delivery' si no existe (en inglés y minúscula)
        $this->info("3️⃣ Verificando rol 'delivery'...");

        $deliveryRole = Role::firstOrCreate(['name' => 'delivery'], [
            'guard_name' => 'web'
        ]);

        $this->line("   ✅ Rol 'delivery' disponible");

        // 4. Asignar rol al usuario
        if ($employee->user) {
            $this->info("4️⃣ Asignando rol 'delivery' al usuario...");

            if (!$employee->user->hasRole('delivery')) {
                $employee->user->assignRole('delivery');
                $this->line("   ✅ Rol asignado");
            } else {
                $this->line("   ℹ️  Usuario ya tiene el rol 'delivery'");
            }
        }

        $this->line("");
        $this->info("🎯 EMPLEADO CORREGIDO EXITOSAMENTE:");
        $this->line("   👤 Nombre: {$employee->full_name}");
        $this->line("   🏷️  Posición: {$employee->position}");
        $this->line("   📧 Usuario: " . ($employee->user ? $employee->user->email : 'Sin usuario'));
        $hasDeliveryRole = $employee->user && ($employee->user->hasRole('delivery') || $employee->user->hasRole('Delivery'));
        $this->line("   🔑 Rol: " . ($hasDeliveryRole ? 'delivery ✅' : 'Sin rol ❌'));

        $this->line("");
        $this->info("🧪 Ahora puedes probar:");
        $this->line("   php artisan delivery:diagnose-assignment");

        return 0;
    }
}
