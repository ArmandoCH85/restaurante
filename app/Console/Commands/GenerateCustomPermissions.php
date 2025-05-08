<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Config;

class GenerateCustomPermissions extends Command
{
    protected $signature = 'shield:custom-permissions';
    protected $description = 'Generate custom permissions defined in filament-shield-custom-permissions.php';

    public function handle()
    {
        $this->info('Generating custom permissions...');
        
        // Obtener los permisos personalizados del archivo de configuración
        $customPermissions = Config::get('filament-shield-custom-permissions', []);
        
        if (empty($customPermissions)) {
            $this->error('No custom permissions defined in config/filament-shield-custom-permissions.php');
            return 1;
        }
        
        $count = 0;
        
        // Crear cada permiso personalizado
        foreach ($customPermissions as $name => $details) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            
            $this->info("Created permission: {$name}");
            $count++;
        }
        
        $this->info("Successfully created {$count} custom permissions.");
        
        return 0;
    }
}
