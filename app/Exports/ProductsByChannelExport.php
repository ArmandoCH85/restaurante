<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductsByChannelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    protected $data;
    protected $period;

    public function __construct($data, $period = null)
    {
        $this->data = $data;
        $this->period = $period;
        
        \Log::info('[EXPORT-CLASS] ProductsByChannelExport instanciado', [
            'total_registros' => $data->count(),
            'period' => $period
        ]);
    }

    public function collection()
    {
        \Log::info('[EXPORT-CLASS] Método collection() llamado', [
            'total_items' => $this->data->count()
        ]);
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Canal de Venta',
            'Cantidad',
            'Total Ventas (S/)'
        ];
    }

    public function map($item): array
    {
        // Traducir service_type a texto legible
        $channelLabels = [
            'dine_in' => 'En Mesa',
            'takeout' => 'Para Llevar',
            'delivery' => 'Delivery',
            'drive_thru' => 'Auto Servicio'
        ];

        $mapped = [
            $item->product_name,
            $channelLabels[$item->service_type] ?? $item->service_type,
            $item->total_quantity,  // Sin number_format para mantenerlo como número
            $item->total_sales     // Sin number_format para mantenerlo como número
        ];
        
        \Log::debug('[EXPORT-CLASS] Mapeando item', [
            'product' => $item->product_name,
            'channel' => $item->service_type,
            'mapped_data' => $mapped
        ]);
        
        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }
}
