<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreditNotePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos para notas de crédito
        $creditNotePermissions = [
            'view_credit::note',
            'view_any_credit::note',
            'create_credit::note',
            'update_credit::note',
            'delete_credit::note',
            'delete_any_credit::note',
        ];

        // Crear permisos si no existen
        foreach ($creditNotePermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Asignar permisos al rol super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($creditNotePermissions);
            $this->command->info('Permisos de notas de crédito asignados al rol super_admin');
        } else {
            $this->command->warn('Rol super_admin no encontrado');
        }

        // También asignar al rol admin si existe
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($creditNotePermissions);
            $this->command->info('Permisos de notas de crédito asignados al rol admin');
        }
    }
}