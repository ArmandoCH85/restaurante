<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            // Meat Suppliers
            [
                'business_name' => 'Carnes San Juan S.A.C.',
                'tax_id' => '20123456789',
                'address' => 'Av. Argentina 1234, Cercado de Lima',
                'phone' => '01-234-5678',
                'email' => 'ventas@carnessanjuan.com',
                'contact_name' => 'Carlos Mendoza',
                'contact_phone' => '987-654-321',
                'active' => true,
            ],
            [
                'business_name' => 'Frigorífico Central Lima S.A.',
                'tax_id' => '20234567890',
                'address' => 'Jr. Huánuco 567, La Victoria',
                'phone' => '01-345-6789',
                'email' => 'pedidos@frigorifico.com.pe',
                'contact_name' => 'María González',
                'contact_phone' => '976-543-210',
                'active' => true,
            ],
            [
                'business_name' => 'Distribuidora Ganadera Norte E.I.R.L.',
                'tax_id' => '20345678901',
                'address' => 'Av. Colonial 890, Callao',
                'phone' => '01-456-7890',
                'email' => 'info@ganaderanorte.pe',
                'contact_name' => 'José Ramírez',
                'contact_phone' => '965-432-109',
                'active' => true,
            ],

            // Vegetable and Fruit Suppliers
            [
                'business_name' => 'Verduras Frescas del Valle S.R.L.',
                'tax_id' => '20456789012',
                'address' => 'Mercado Mayorista de Lima, Stand 45-47',
                'phone' => '01-567-8901',
                'email' => 'ventas@verdurasdelvalle.com',
                'contact_name' => 'Ana Torres',
                'contact_phone' => '954-321-098',
                'active' => true,
            ],
            [
                'business_name' => 'Productos Agrícolas Andinos S.A.',
                'tax_id' => '20567890123',
                'address' => 'Av. Aviación 2345, San Borja',
                'phone' => '01-678-9012',
                'email' => 'contacto@agricolaandinos.pe',
                'contact_name' => 'Pedro Huamán',
                'contact_phone' => '943-210-987',
                'active' => true,
            ],

            // Seafood Suppliers
            [
                'business_name' => 'Pescados y Mariscos del Callao S.A.C.',
                'tax_id' => '20678901234',
                'address' => 'Terminal Pesquero del Callao, Módulo 12',
                'phone' => '01-789-0123',
                'email' => 'ventas@mariscoscallao.com',
                'contact_name' => 'Roberto Silva',
                'contact_phone' => '932-109-876',
                'active' => true,
            ],
            [
                'business_name' => 'Distribuidora Marina Lima E.I.R.L.',
                'tax_id' => '20789012345',
                'address' => 'Av. Nestor Gambetta 1567, Callao',
                'phone' => '01-890-1234',
                'email' => 'pedidos@marinalima.pe',
                'contact_name' => 'Carmen Vega',
                'contact_phone' => '921-098-765',
                'active' => true,
            ],

            // Dairy Suppliers
            [
                'business_name' => 'Lácteos Gloria S.A.',
                'tax_id' => '20890123456',
                'address' => 'Av. República de Panamá 2461, La Victoria',
                'phone' => '01-901-2345',
                'email' => 'distribuidores@gloria.com.pe',
                'contact_name' => 'Luis Morales',
                'contact_phone' => '910-987-654',
                'active' => true,
            ],
            [
                'business_name' => 'Laive S.A.',
                'tax_id' => '20901234567',
                'address' => 'Av. Nicolás Arriola 740, La Victoria',
                'phone' => '01-012-3456',
                'email' => 'ventas@laive.pe',
                'contact_name' => 'Patricia Díaz',
                'contact_phone' => '909-876-543',
                'active' => true,
            ],

            // Beverage Suppliers
            [
                'business_name' => 'Distribuidora de Bebidas Lima S.A.C.',
                'tax_id' => '21012345678',
                'address' => 'Av. Argentina 3456, Cercado de Lima',
                'phone' => '01-123-4567',
                'email' => 'ventas@bebidaslima.com',
                'contact_name' => 'Fernando Castro',
                'contact_phone' => '998-765-432',
                'active' => true,
            ],
            [
                'business_name' => 'Backus y Johnston S.A.A.',
                'tax_id' => '21123456789',
                'address' => 'Av. Nicolás Ayllón 3986, Ate',
                'phone' => '01-234-5679',
                'email' => 'distribuidores@backus.pe',
                'contact_name' => 'Sandra López',
                'contact_phone' => '987-654-321',
                'active' => true,
            ],

            // Grain and Dry Goods Suppliers
            [
                'business_name' => 'Alimentos Procesados del Perú S.A.',
                'tax_id' => '21234567890',
                'address' => 'Av. Industrial 1890, Villa El Salvador',
                'phone' => '01-345-6780',
                'email' => 'ventas@alimentosperu.com',
                'contact_name' => 'Miguel Herrera',
                'contact_phone' => '976-543-210',
                'active' => true,
            ],
            [
                'business_name' => 'Distribuidora de Abarrotes Central S.R.L.',
                'tax_id' => '21345678901',
                'address' => 'Jr. Paruro 678, Cercado de Lima',
                'phone' => '01-456-7891',
                'email' => 'pedidos@abarrotescentral.pe',
                'contact_name' => 'Rosa Fernández',
                'contact_phone' => '965-432-109',
                'active' => true,
            ],

            // Spices and Condiments Suppliers
            [
                'business_name' => 'Especias y Condimentos del Perú E.I.R.L.',
                'tax_id' => '21456789012',
                'address' => 'Mercado Central, Puestos 234-236',
                'phone' => '01-567-8902',
                'email' => 'info@especiasperu.com',
                'contact_name' => 'Alberto Quispe',
                'contact_phone' => '954-321-098',
                'active' => true,
            ],

            // Oil and Vinegar Suppliers
            [
                'business_name' => 'Aceites Premium del Perú S.A.C.',
                'tax_id' => '21567890123',
                'address' => 'Av. Universitaria 1456, San Martín de Porres',
                'phone' => '01-678-9013',
                'email' => 'ventas@aceitespremium.pe',
                'contact_name' => 'Elena Vargas',
                'contact_phone' => '943-210-987',
                'active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        $this->command->info('Suppliers seeded successfully!');
    }
}
