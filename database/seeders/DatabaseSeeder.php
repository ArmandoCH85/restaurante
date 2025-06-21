<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders del sistema de restaurante...');

        // 1️⃣ CONFIGURACIÓN BÁSICA
        $this->command->info('📋 Configuración básica...');
        $this->call([
            AppSettingsSeeder::class,
            DocumentSeriesSeeder::class,
        ]);

        // 2️⃣ ROLES Y PERMISOS
        $this->command->info('🔐 Roles y permisos...');
        $this->call([
            ShieldSeeder::class,
            RoleSeeder::class,
        ]);

        // 3️⃣ ESTRUCTURA DEL RESTAURANTE
        $this->command->info('🏢 Estructura del restaurante...');
        $this->call([
            FloorSeeder::class,
            TablesTableSeeder::class,
            WarehouseSeeder::class,
        ]);

        // 4️⃣ PROVEEDORES E INGREDIENTES
        $this->command->info('📦 Inventario y proveedores...');
        $this->call([
            SupplierSeeder::class,
            IngredientSeeder::class,
            IngredientStockSeeder::class,
        ]);

        // 5️⃣ PRODUCTOS Y MENÚ
        $this->command->info('🍽️ Productos y menú...');
        $this->call([
            RestaurantMenuSeeder::class,
            // RecipeSeeder::class, // Temporalmente comentado - problema de foreign key
        ]);

        // 6️⃣ PERSONAS
        $this->command->info('👥 Empleados y clientes...');
        $this->call([
            EmployeeSeeder::class,
            CustomerSeeder::class,
        ]);

        // 7️⃣ USUARIO ADMINISTRADOR
        $this->command->info('👤 Creando usuario administrador...');
        $user = User::firstOrCreate(
            ['email' => 'admin@restaurante.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol super admin si existe el paquete Shield
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
            $user->assignRole($superAdminRole);
            $this->command->info("✅ Usuario admin creado: {$user->email} | Contraseña: admin123");
        }

        $this->command->info('🎉 ¡Todos los seeders ejecutados exitosamente!');
        $this->command->info('🌐 Accede a: http://restaurante.test/admin');
        $this->command->info('👤 Email: admin@restaurante.com | Contraseña: admin123');
    }
}
