<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;

class CheckUserRoles extends Command
{
    protected $signature = 'users:check-roles {email}';
    protected $description = 'Verificar roles de un usuario específico';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Usuario con email {$email} no encontrado");
            return 1;
        }

        $this->info("👤 INFORMACIÓN DEL USUARIO:");
        $this->line("   📧 Email: {$user->email}");
        $this->line("   👤 Nombre: {$user->name}");
        $this->line("   📅 Creado: {$user->created_at}");
        $this->line("");

        $this->info("🔑 ROLES ASIGNADOS:");
        $roles = $user->roles;
        
        if ($roles->isEmpty()) {
            $this->line("   ❌ No tiene roles asignados");
        } else {
            foreach ($roles as $role) {
                $this->line("   ✅ {$role->name}");
            }
        }

        $this->line("");
        $this->info("👨‍💼 EMPLEADO ASOCIADO:");
        $employee = Employee::where('user_id', $user->id)->first();
        
        if ($employee) {
            $this->line("   ✅ ID: {$employee->id}");
            $this->line("   👤 Nombre: {$employee->full_name}");
            $this->line("   🏷️  Posición: {$employee->position}");
        } else {
            $this->line("   ❌ No hay empleado asociado");
        }

        return 0;
    }
}
