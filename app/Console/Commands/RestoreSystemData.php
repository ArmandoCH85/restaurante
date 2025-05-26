<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Floor;
use App\Models\Table;
use App\Models\CashRegister;
use App\Models\Ingredient;
use App\Models\AppSetting;

class RestoreSystemData extends Command
{
    protected $signature = 'system:restore-data {--module=all}';
    protected $description = 'Restaurar datos básicos del sistema restaurante';

    public function handle()
    {
        $this->info('🔄 Restaurando datos básicos del sistema...');
        $this->line('');

        $module = $this->option('module');

        switch ($module) {
            case 'employees':
                return $this->restoreEmployees();
            case 'customers':
                return $this->restoreCustomers();
            case 'suppliers':
                return $this->restoreSuppliers();
            case 'products':
                return $this->restoreProducts();
            case 'warehouse':
                return $this->restoreWarehouses();
            case 'restaurant':
                return $this->restoreRestaurantStructure();
            case 'cash':
                return $this->restoreCashRegisters();
            case 'ingredients':
                return $this->restoreIngredients();
            case 'settings':
                return $this->restoreSettings();
            case 'all':
            default:
                return $this->restoreAll();
        }
    }

    private function restoreAll()
    {
        $this->info('📋 Restaurando todos los módulos...');
        $this->line('');

        $modules = [
            'Empleados' => 'restoreEmployees',
            'Almacenes' => 'restoreWarehouses',
            'Categorías de Productos' => 'restoreProductCategories',
            'Productos' => 'restoreProducts',
            'Ingredientes' => 'restoreIngredients',
            'Estructura del Restaurante' => 'restoreRestaurantStructure',
            'Cajas Registradoras' => 'restoreCashRegisters',
            'Clientes Adicionales' => 'restoreCustomers',
            'Proveedores' => 'restoreSuppliers',
            'Configuraciones Adicionales' => 'restoreSettings'
        ];

        $success = 0;
        $failed = 0;

        foreach ($modules as $moduleName => $method) {
            $this->line("🔄 {$moduleName}...");
            try {
                $this->$method();
                $this->line("   ✅ Completado");
                $success++;
            } catch (\Exception $e) {
                $this->line("   ❌ Error: " . $e->getMessage());
                $failed++;
            }
            $this->line('');
        }

        $this->line('');
        $this->info("📊 Resumen: {$success} módulos restaurados, {$failed} errores");
        
        if ($failed === 0) {
            $this->info('🎉 ¡Sistema completamente restaurado!');
        }

        return $failed === 0 ? 0 : 1;
    }

    private function restoreEmployees()
    {
        $employees = [
            [
                'name' => 'Juan Pérez',
                'position' => 'Cajero',
                'phone' => '987654321',
                'email' => 'juan.perez@restaurante.com',
                'hire_date' => now()->subMonths(6),
                'salary' => 1200.00,
                'active' => true
            ],
            [
                'name' => 'María García',
                'position' => 'Mesera',
                'phone' => '987654322',
                'email' => 'maria.garcia@restaurante.com',
                'hire_date' => now()->subMonths(4),
                'salary' => 1000.00,
                'active' => true
            ],
            [
                'name' => 'Carlos López',
                'position' => 'Cocinero',
                'phone' => '987654323',
                'email' => 'carlos.lopez@restaurante.com',
                'hire_date' => now()->subMonths(8),
                'salary' => 1500.00,
                'active' => true
            ],
            [
                'name' => 'Ana Rodríguez',
                'position' => 'Administradora',
                'phone' => '987654324',
                'email' => 'ana.rodriguez@restaurante.com',
                'hire_date' => now()->subYear(),
                'salary' => 2000.00,
                'active' => true
            ]
        ];

        foreach ($employees as $employeeData) {
            Employee::updateOrCreate(
                ['email' => $employeeData['email']],
                $employeeData
            );
        }

        $this->line("   ✅ " . count($employees) . " empleados creados");
        return true;
    }

    private function restoreWarehouses()
    {
        $warehouses = [
            [
                'name' => 'Almacén Principal',
                'description' => 'Almacén principal del restaurante',
                'location' => 'Planta baja',
                'active' => true
            ],
            [
                'name' => 'Almacén de Bebidas',
                'description' => 'Almacén específico para bebidas',
                'location' => 'Sótano',
                'active' => true
            ],
            [
                'name' => 'Almacén de Ingredientes',
                'description' => 'Almacén para ingredientes frescos',
                'location' => 'Cocina',
                'active' => true
            ]
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::updateOrCreate(
                ['name' => $warehouseData['name']],
                $warehouseData
            );
        }

        $this->line("   ✅ " . count($warehouses) . " almacenes creados");
        return true;
    }

    private function restoreProductCategories()
    {
        $categories = [
            [
                'name' => 'Bebidas',
                'description' => 'Bebidas frías y calientes',
                'active' => true
            ],
            [
                'name' => 'Platos Principales',
                'description' => 'Platos principales del menú',
                'active' => true
            ],
            [
                'name' => 'Entradas',
                'description' => 'Entradas y aperitivos',
                'active' => true
            ],
            [
                'name' => 'Postres',
                'description' => 'Postres y dulces',
                'active' => true
            ],
            [
                'name' => 'Pollos',
                'description' => 'Especialidades de pollo',
                'active' => true
            ],
            [
                'name' => 'Acompañamientos',
                'description' => 'Guarniciones y acompañamientos',
                'active' => true
            ]
        ];

        foreach ($categories as $categoryData) {
            ProductCategory::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        $this->line("   ✅ " . count($categories) . " categorías creadas");
        return true;
    }

    private function restoreProducts()
    {
        // Primero asegurar que existan categorías
        $this->restoreProductCategories();

        $bebidas = ProductCategory::where('name', 'Bebidas')->first();
        $principales = ProductCategory::where('name', 'Platos Principales')->first();
        $pollos = ProductCategory::where('name', 'Pollos')->first();
        $acompañamientos = ProductCategory::where('name', 'Acompañamientos')->first();

        $products = [
            // Bebidas
            [
                'name' => 'Inca Kola 500ml',
                'description' => 'Gaseosa Inca Kola de 500ml',
                'price' => 3.50,
                'cost' => 2.00,
                'stock' => 100,
                'min_stock' => 20,
                'category_id' => $bebidas->id,
                'active' => true
            ],
            [
                'name' => 'Coca Cola 500ml',
                'description' => 'Gaseosa Coca Cola de 500ml',
                'price' => 3.50,
                'cost' => 2.00,
                'stock' => 100,
                'min_stock' => 20,
                'category_id' => $bebidas->id,
                'active' => true
            ],
            [
                'name' => 'Chicha Morada',
                'description' => 'Chicha morada casera',
                'price' => 4.00,
                'cost' => 1.50,
                'stock' => 50,
                'min_stock' => 10,
                'category_id' => $bebidas->id,
                'active' => true
            ],
            // Pollos
            [
                'name' => 'Pollo a la Brasa 1/4',
                'description' => 'Cuarto de pollo a la brasa',
                'price' => 12.00,
                'cost' => 6.00,
                'stock' => 50,
                'min_stock' => 10,
                'category_id' => $pollos->id,
                'active' => true
            ],
            [
                'name' => 'Pollo a la Brasa 1/2',
                'description' => 'Medio pollo a la brasa',
                'price' => 22.00,
                'cost' => 11.00,
                'stock' => 30,
                'min_stock' => 5,
                'category_id' => $pollos->id,
                'active' => true
            ],
            [
                'name' => 'Pollo a la Brasa Entero',
                'description' => 'Pollo entero a la brasa',
                'price' => 40.00,
                'cost' => 20.00,
                'stock' => 20,
                'min_stock' => 3,
                'category_id' => $pollos->id,
                'active' => true
            ],
            // Acompañamientos
            [
                'name' => 'Papas Fritas',
                'description' => 'Porción de papas fritas',
                'price' => 6.00,
                'cost' => 2.50,
                'stock' => 100,
                'min_stock' => 20,
                'category_id' => $acompañamientos->id,
                'active' => true
            ],
            [
                'name' => 'Ensalada Criolla',
                'description' => 'Ensalada criolla tradicional',
                'price' => 4.00,
                'cost' => 1.50,
                'stock' => 50,
                'min_stock' => 10,
                'category_id' => $acompañamientos->id,
                'active' => true
            ]
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        $this->line("   ✅ " . count($products) . " productos creados");
        return true;
    }
}
