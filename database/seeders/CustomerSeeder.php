<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla antes de poblarla
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Customer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cliente genérico para notas de venta
        Customer::create([
            'document_type' => 'DNI',
            'document_number' => '00000000',
            'name' => 'Cliente Genérico',
            'address' => 'Consumidor Final',
            'tax_validated' => false
        ]);

        // Personas naturales con DNI
        Customer::create([
            'document_type' => 'DNI',
            'document_number' => '12345678',
            'name' => 'Juan Pérez',
            'phone' => '987654321',
            'email' => 'juan.perez@example.com',
            'address' => 'Av. Principal 123, Lima',
            'tax_validated' => true
        ]);

        Customer::create([
            'document_type' => 'DNI',
            'document_number' => '87654321',
            'name' => 'María Rodríguez',
            'phone' => '978456123',
            'email' => 'maria.rodriguez@example.com',
            'address' => 'Calle Las Flores 456, Lima',
            'tax_validated' => true
        ]);

        // Empresas con RUC
        Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20123456789',
            'name' => 'Empresa ABC S.A.C.',
            'phone' => '015487965',
            'email' => 'ventas@empresaabc.com',
            'address' => 'Av. Industrial 789, Lima',
            'address_references' => 'Cerca al parque industrial',
            'tax_validated' => true
        ]);

        Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20987654321',
            'name' => 'Corporación XYZ E.I.R.L.',
            'phone' => '017896541',
            'email' => 'contacto@corpxyz.com',
            'address' => 'Jr. Comercio 567, Lima',
            'tax_validated' => true
        ]);
    }
}
