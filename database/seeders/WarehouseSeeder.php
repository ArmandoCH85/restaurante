<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user or create a default one
        $adminUser = User::where('email', 'admin@restaurante.com')->first();
        if (!$adminUser) {
            $adminUser = User::first();
        }

        $warehouses = [
            [
                'name' => 'Almacén Principal',
                'code' => 'ALM01',
                'description' => 'Almacén principal del restaurante para ingredientes y productos generales',
                'location' => 'Planta Baja - Área Central',
                'is_default' => true,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Cámara Frigorífica',
                'code' => 'FRZ01',
                'description' => 'Cámara frigorífica para productos congelados y carnes',
                'location' => 'Área de Refrigeración - Lado Izquierdo',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Refrigerador Principal',
                'code' => 'REF01',
                'description' => 'Refrigerador para productos frescos, lácteos y verduras',
                'location' => 'Cocina - Área de Refrigeración',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Almacén de Secos',
                'code' => 'SEC01',
                'description' => 'Almacén para productos secos, granos, condimentos y no perecederos',
                'location' => 'Depósito General - Estantería A',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Almacén de Bebidas',
                'code' => 'BEB01',
                'description' => 'Almacén especializado para bebidas, gaseosas y licores',
                'location' => 'Área de Bar - Depósito Posterior',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Almacén de Cocina',
                'code' => 'COC01',
                'description' => 'Almacén ubicado en la cocina para ingredientes de uso diario',
                'location' => 'Cocina Principal - Despensa',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Área de Recepción',
                'code' => 'REC01',
                'description' => 'Área temporal para recepción y verificación de mercadería',
                'location' => 'Zona de Carga y Descarga',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Almacén de Limpieza',
                'code' => 'LIM01',
                'description' => 'Almacén para productos de limpieza y desinfección',
                'location' => 'Área de Servicio - Cuarto de Limpieza',
                'is_default' => false,
                'active' => true,
                'created_by' => $adminUser?->id,
            ],
            [
                'name' => 'Almacén de Emergencia',
                'code' => 'EMR01',
                'description' => 'Almacén de reserva para situaciones de emergencia',
                'location' => 'Sótano - Área Restringida',
                'is_default' => false,
                'active' => false, // Inactive by default
                'created_by' => $adminUser?->id,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::create($warehouseData);
        }

        $this->command->info('Warehouses seeded successfully!');
        $this->command->info('Default warehouse: Almacén Principal (ALM01)');
    }
}
