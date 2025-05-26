<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get suppliers by business name patterns
        $meatSupplier = Supplier::where('business_name', 'like', '%Carnes%')->first();
        $vegetableSupplier = Supplier::where('business_name', 'like', '%Verduras%')->first();
        $seafoodSupplier = Supplier::where('business_name', 'like', '%Pescados%')->first();
        $dairySupplier = Supplier::where('business_name', 'like', '%Lácteos%')->first();
        $grainSupplier = Supplier::where('business_name', 'like', '%Alimentos%')->first();
        $spiceSupplier = Supplier::where('business_name', 'like', '%Especias%')->first();
        $oilSupplier = Supplier::where('business_name', 'like', '%Aceites%')->first();

        // Fallback to first supplier if specific ones not found
        $defaultSupplier = Supplier::first();

        $ingredients = [
            // Carnes y Aves
            [
                'name' => 'Pollo Entero',
                'code' => 'CAR001',
                'description' => 'Pollo fresco entero de granja',
                'unit_of_measure' => 'KG',
                'min_stock' => 10.000,
                'current_stock' => 25.500,
                'current_cost' => 18.50,
                'supplier_id' => $meatSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Carne de Res',
                'code' => 'CAR002',
                'description' => 'Carne de res fresca para bistec',
                'unit_of_measure' => 'KG',
                'min_stock' => 8.000,
                'current_stock' => 15.750,
                'current_cost' => 32.00,
                'supplier_id' => $meatSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Cerdo',
                'code' => 'CAR003',
                'description' => 'Carne de cerdo fresca',
                'unit_of_measure' => 'KG',
                'min_stock' => 5.000,
                'current_stock' => 12.250,
                'current_cost' => 24.00,
                'supplier_id' => $meatSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Pescados y Mariscos
            [
                'name' => 'Pescado Fresco',
                'code' => 'PES001',
                'description' => 'Pescado fresco del día para ceviche',
                'unit_of_measure' => 'KG',
                'min_stock' => 5.000,
                'current_stock' => 8.500,
                'current_cost' => 28.00,
                'supplier_id' => $seafoodSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Camarones',
                'code' => 'PES002',
                'description' => 'Camarones frescos medianos',
                'unit_of_measure' => 'KG',
                'min_stock' => 3.000,
                'current_stock' => 6.750,
                'current_cost' => 45.00,
                'supplier_id' => $seafoodSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Verduras y Hortalizas
            [
                'name' => 'Cebolla Roja',
                'code' => 'VER001',
                'description' => 'Cebolla roja fresca nacional',
                'unit_of_measure' => 'KG',
                'min_stock' => 15.000,
                'current_stock' => 35.250,
                'current_cost' => 2.50,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Tomate',
                'code' => 'VER002',
                'description' => 'Tomate fresco de primera calidad',
                'unit_of_measure' => 'KG',
                'min_stock' => 10.000,
                'current_stock' => 22.500,
                'current_cost' => 3.20,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Papa Blanca',
                'code' => 'VER003',
                'description' => 'Papa blanca para freír y sancochar',
                'unit_of_measure' => 'KG',
                'min_stock' => 25.000,
                'current_stock' => 45.750,
                'current_cost' => 1.80,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Ajo',
                'code' => 'VER004',
                'description' => 'Ajo fresco nacional',
                'unit_of_measure' => 'KG',
                'min_stock' => 2.000,
                'current_stock' => 5.500,
                'current_cost' => 8.50,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Cilantro',
                'code' => 'VER005',
                'description' => 'Cilantro fresco en atados',
                'unit_of_measure' => 'KG',
                'min_stock' => 1.000,
                'current_stock' => 3.250,
                'current_cost' => 4.00,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Lácteos
            [
                'name' => 'Leche Fresca',
                'code' => 'LAC001',
                'description' => 'Leche fresca entera pasteurizada',
                'unit_of_measure' => 'LT',
                'min_stock' => 20.000,
                'current_stock' => 35.500,
                'current_cost' => 4.20,
                'supplier_id' => $dairySupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Queso Fresco',
                'code' => 'LAC002',
                'description' => 'Queso fresco de vaca',
                'unit_of_measure' => 'KG',
                'min_stock' => 5.000,
                'current_stock' => 12.750,
                'current_cost' => 16.00,
                'supplier_id' => $dairySupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Mantequilla',
                'code' => 'LAC003',
                'description' => 'Mantequilla sin sal',
                'unit_of_measure' => 'KG',
                'min_stock' => 2.000,
                'current_stock' => 4.500,
                'current_cost' => 22.00,
                'supplier_id' => $dairySupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Granos y Cereales
            [
                'name' => 'Arroz Extra',
                'code' => 'GRA001',
                'description' => 'Arroz extra de grano largo',
                'unit_of_measure' => 'KG',
                'min_stock' => 50.000,
                'current_stock' => 125.500,
                'current_cost' => 4.50,
                'supplier_id' => $grainSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Frijoles Canarios',
                'code' => 'GRA002',
                'description' => 'Frijoles canarios secos',
                'unit_of_measure' => 'KG',
                'min_stock' => 10.000,
                'current_stock' => 25.750,
                'current_cost' => 6.80,
                'supplier_id' => $grainSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Harina de Trigo',
                'code' => 'GRA003',
                'description' => 'Harina de trigo sin preparar',
                'unit_of_measure' => 'KG',
                'min_stock' => 15.000,
                'current_stock' => 35.250,
                'current_cost' => 3.20,
                'supplier_id' => $grainSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Condimentos y Especias
            [
                'name' => 'Sal de Mesa',
                'code' => 'CON001',
                'description' => 'Sal de mesa refinada',
                'unit_of_measure' => 'GR',
                'min_stock' => 2000.000,
                'current_stock' => 5500.000,
                'current_cost' => 0.002,
                'supplier_id' => $spiceSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Pimienta Negra',
                'code' => 'CON002',
                'description' => 'Pimienta negra molida',
                'unit_of_measure' => 'GR',
                'min_stock' => 500.000,
                'current_stock' => 1250.000,
                'current_cost' => 0.015,
                'supplier_id' => $spiceSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Comino Molido',
                'code' => 'CON003',
                'description' => 'Comino molido para condimentar',
                'unit_of_measure' => 'GR',
                'min_stock' => 300.000,
                'current_stock' => 750.000,
                'current_cost' => 0.020,
                'supplier_id' => $spiceSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Aceites y Vinagres
            [
                'name' => 'Aceite Vegetal',
                'code' => 'ACE001',
                'description' => 'Aceite vegetal para freír',
                'unit_of_measure' => 'LT',
                'min_stock' => 10.000,
                'current_stock' => 25.500,
                'current_cost' => 12.50,
                'supplier_id' => $oilSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Aceite de Oliva',
                'code' => 'ACE002',
                'description' => 'Aceite de oliva extra virgen',
                'unit_of_measure' => 'LT',
                'min_stock' => 3.000,
                'current_stock' => 8.250,
                'current_cost' => 28.00,
                'supplier_id' => $oilSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],

            // Otros ingredientes esenciales
            [
                'name' => 'Huevos',
                'code' => 'OTR001',
                'description' => 'Huevos frescos de gallina',
                'unit_of_measure' => 'UND',
                'min_stock' => 100.000,
                'current_stock' => 250.000,
                'current_cost' => 0.45,
                'supplier_id' => $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Azúcar Blanca',
                'code' => 'OTR002',
                'description' => 'Azúcar blanca refinada',
                'unit_of_measure' => 'KG',
                'min_stock' => 10.000,
                'current_stock' => 22.500,
                'current_cost' => 3.80,
                'supplier_id' => $grainSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
            [
                'name' => 'Limón',
                'code' => 'FRU001',
                'description' => 'Limón fresco nacional',
                'unit_of_measure' => 'UND',
                'min_stock' => 50.000,
                'current_stock' => 125.000,
                'current_cost' => 0.25,
                'supplier_id' => $vegetableSupplier?->id ?? $defaultSupplier->id,
                'active' => true,
            ],
        ];

        foreach ($ingredients as $ingredientData) {
            Ingredient::create($ingredientData);
        }

        $this->command->info('Ingredients seeded successfully!');
    }
}
