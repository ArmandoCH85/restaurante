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
        
        // Calcular totales
        $totalQuantity = $this->data->sum('total_quantity');
        $totalSales = $this->data->sum('total_sales');
        
        // Crear objeto de totales con la misma estructura que los items
        $totalsRow = (object) [
            'product_name' => 'TOTAL GENERAL',
            'service_type' => '',
            'total_quantity' => $totalQuantity,
            'total_sales' => $totalSales
        ];
        
        // Agregar fila de totales al final
        $dataWithTotals = $this->data->push($totalsRow);
        
        \Log::info('[EXPORT-CLASS] Totales agregados', [
            'total_quantity' => $totalQuantity,
            'total_sales' => $totalSales
        ]);
        
        return $dataWithTotals;
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
        // Calcular el número de la última fila (encabezado + datos + totales)
        $lastRow = $this->data->count() + 2; // +1 por encabezado, +1 por fila de totales
        
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
            // Estilo para la fila de totales
            $lastRow => [
                'font' => [
                    'bold' => true,
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6']
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['rgb' => '000000']
                    ]
                ]
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
