<?php

namespace Database\Seeders;

use App\Models\DocumentSeries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = [
            [
                'document_type' => 'credit_note',
                'series' => 'FC01',
                'current_number' => 1,
                'active' => true,
                'description' => 'Serie principal para Notas de Crédito'
            ],
            [
                'document_type' => 'invoice',
                'series' => 'F001',
                'current_number' => 1,
                'active' => true,
                'description' => 'Serie principal para Facturas'
            ],
            [
                'document_type' => 'receipt',
                'series' => 'B001',
                'current_number' => 1,
                'active' => true,
                'description' => 'Serie principal para Boletas'
            ],
            [
                'document_type' => 'sales_note',
                'series' => 'NV001',
                'current_number' => 1,
                'active' => true,
                'description' => 'Serie principal para Notas de Venta'
            ],
        ];

        foreach ($series as $serie) {
            DocumentSeries::updateOrCreate(
                [
                    'document_type' => $serie['document_type'],
                    'series' => $serie['series']
                ],
                $serie
            );
        }
        
        // Desactivar series antiguas de notas de crédito que no sean FC01
        DocumentSeries::where('document_type', 'credit_note')
            ->where('series', '!=', 'FC01')
            ->update(['active' => false]);
    }
}
