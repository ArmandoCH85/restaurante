<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixQuotationPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix-quotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix quotation permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing quotation permissions...');

        // Lista de permisos necesarios
        $permissions = [
            'view_any_quotation',
            'view_quotation',
            'create_quotation',
            'update_quotation',
            'delete_quotation',
            'restore_quotation',
            'force_delete_quotation',
            'approve_quotation',
            'reject_quotation',
            'send_quotation',
            'convert_quotation_to_order',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            try {
                $existingPermission = Permission::where('name', $permission)->first();
                
                if (!$existingPermission) {
                    Permission::create(['name' => $permission, 'guard_name' => 'web']);
                    $this->info("Permission '{$permission}' created successfully.");
                } else {
                    $this->info("Permission '{$permission}' already exists.");
                }
            } catch (\Exception $e) {
                $this->error("Error creating permission '{$permission}': " . $e->getMessage());
            }
        }

        // Asignar permisos a roles
        $this->assignPermissionsToRoles($permissions);

        $this->info('Quotation permissions fixed successfully.');
    }

    /**
     * Asignar permisos a roles.
     */
    private function assignPermissionsToRoles(array $permissions): void
    {
        // Obtener roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        // Asignar todos los permisos al super_admin
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
            $this->info('Permissions assigned to super_admin role.');
        } else {
            $this->warn('super_admin role not found.');
        }

        // Asignar permisos al admin
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            $this->info('Permissions assigned to admin role.');
        } else {
            $this->warn('admin role not found.');
        }

        // Asignar permisos al manager (excepto force_delete)
        if ($managerRole) {
            $managerPermissions = array_filter($permissions, function ($permission) {
                return $permission !== 'force_delete_quotation';
            });
            $managerRole->givePermissionTo($managerPermissions);
            $this->info('Permissions assigned to manager role.');
        } else {
            $this->warn('manager role not found.');
        }

        // Asignar permisos básicos al cashier
        if ($cashierRole) {
            $cashierPermissions = [
                'view_any_quotation',
                'view_quotation',
                'create_quotation',
                'update_quotation',
                'send_quotation',
                'convert_quotation_to_order',
            ];
            $cashierRole->givePermissionTo($cashierPermissions);
            $this->info('Permissions assigned to cashier role.');
        } else {
            $this->warn('cashier role not found.');
        }
    }
}
