<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles básicos
        $roles = [
            'super_admin' => 'Administrador con acceso total',
            'admin' => 'Administrador del sistema',
            'waiter' => 'Mesero',
            'cashier' => 'Cajero',
            'kitchen' => 'Cocina',
            'delivery' => 'Repartidor',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(['name' => $name], [
                'guard_name' => 'web',
            ]);
        }

        // Asegurarnos de que existe el super_admin
        $superAdminUser = User::where('email', 'admin@restaurant.com')->first();

        if ($superAdminUser) {
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if ($superAdminRole && !$superAdminUser->hasRole('super_admin')) {
                $superAdminUser->assignRole($superAdminRole);
            }
        }

        // Asignar todos los permisos al super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if ($superAdminRole) {
            $permissions = Permission::all();
            $superAdminRole->syncPermissions($permissions);
        }

        $this->command->info('Roles básicos creados correctamente.');
    }
}
