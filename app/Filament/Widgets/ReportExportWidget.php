<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Livewire\Attributes\On;

class ReportExportWidget extends Widget
{
    protected static string $view = 'filament.widgets.report-export-widget-simple';

    protected int | string | array $columnSpan = 'full';

    public ?string $reportType = 'sales';
    public ?string $dateRange = 'week';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $format = 'pdf';

    public function mount(): void
    {
        $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('reportType')
                ->label('Tipo de Reporte')
                ->options([
                    'sales' => 'Ventas',
                    'profits' => 'Ganancias',
                    'products' => 'Productos Vendidos',
                    'service_types' => 'Ventas por Tipo de Servicio',
                ])
                ->default('sales')
                ->required(),

            Select::make('dateRange')
                ->label('Período')
                ->options([
                    'today' => 'Hoy',
                    'yesterday' => 'Ayer',
                    'week' => 'Esta semana',
                    'month' => 'Este mes',
                    'custom' => 'Personalizado',
                ])
                ->default('week')
                ->live()
                ->afterStateUpdated(function ($state) {
                    if ($state !== 'custom') {
                        $this->startDate = match ($state) {
                            'today' => Carbon::today()->format('Y-m-d'),
                            'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                            'week' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                            'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                            default => Carbon::now()->startOfWeek()->format('Y-m-d'),
                        };

                        $this->endDate = match ($state) {
                            'today' => Carbon::today()->format('Y-m-d'),
                            'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                            default => Carbon::now()->format('Y-m-d'),
                        };
                    }
                })
                ->required(),

            DatePicker::make('startDate')
                ->label('Fecha Inicio')
                ->format('Y-m-d')
                ->displayFormat('d/m/Y')
                ->default(Carbon::now()->startOfWeek())
                ->visible(fn ($get) => $get('dateRange') === 'custom')
                ->required(),

            DatePicker::make('endDate')
                ->label('Fecha Fin')
                ->format('Y-m-d')
                ->displayFormat('d/m/Y')
                ->default(Carbon::now())
                ->visible(fn ($get) => $get('dateRange') === 'custom')
                ->required(),

            Select::make('format')
                ->label('Formato')
                ->options([
                    'pdf' => 'PDF',
                    'excel' => 'Excel',
                ])
                ->default('pdf')
                ->required(),
        ];
    }

    public function exportReport(): void
    {
        // Obtener fechas
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        // Generar el reporte según el tipo seleccionado
        try {
            switch ($this->reportType) {
                case 'sales':
                    $this->exportSalesReport($startDate, $endDate);
                    break;
                case 'profits':
                    $this->exportProfitsReport($startDate, $endDate);
                    break;
                case 'products':
                    $this->exportProductsReport($startDate, $endDate);
                    break;
                case 'service_types':
                    $this->exportServiceTypesReport($startDate, $endDate);
                    break;
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Reporte generado correctamente. La descarga comenzará en breve.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'danger',
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ]);
        }
    }

    #[On('download-pdf')]
    public function downloadPdf($data): void
    {
        // Este método es llamado por el evento JavaScript
        // No necesita hacer nada, ya que la descarga se maneja en el frontend
    }

    #[On('download-excel')]
    public function downloadExcel($data): void
    {
        // Este método es llamado por el evento JavaScript
        // No necesita hacer nada, ya que la descarga se maneja en el frontend
    }

    private function exportSalesReport(Carbon $startDate, Carbon $endDate): void
    {
        // Obtener datos de ventas
        $salesData = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select(
                DB::raw('DATE(order_datetime) as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(total) as average')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalSales = $salesData->sum('total');
        $totalOrders = $salesData->sum('count');
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Generar el reporte según el formato seleccionado
        if ($this->format === 'pdf') {
            $pdf = PDF::loadView('reports.sales', [
                'salesData' => $salesData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'averageTicket' => $averageTicket,
            ]);

            $filename = 'ventas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';

            // Enviar directamente al navegador
            $this->dispatch('download-pdf', [
                'content' => base64_encode($pdf->output()),
                'filename' => $filename
            ]);
        } else {
            // Crear archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'Reporte de Ventas');
            $sheet->setCellValue('A2', 'Período: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
            $sheet->setCellValue('A4', 'Fecha');
            $sheet->setCellValue('B4', 'Total Ventas (S/)');
            $sheet->setCellValue('C4', 'Cantidad de Órdenes');
            $sheet->setCellValue('D4', 'Ticket Promedio (S/)');

            // Datos
            $row = 5;
            foreach ($salesData as $data) {
                $sheet->setCellValue('A' . $row, Carbon::parse($data->date)->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, $data->total);
                $sheet->setCellValue('C' . $row, $data->count);
                $sheet->setCellValue('D' . $row, $data->average);
                $row++;
            }

            // Totales
            $sheet->setCellValue('A' . ($row + 1), 'TOTALES');
            $sheet->setCellValue('B' . ($row + 1), $totalSales);
            $sheet->setCellValue('C' . ($row + 1), $totalOrders);
            $sheet->setCellValue('D' . ($row + 1), $averageTicket);

            // Guardar archivo
            $filename = 'ventas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';

            // Guardar en memoria
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Enviar directamente al navegador
            $this->dispatch('download-excel', [
                'content' => base64_encode($content),
                'filename' => $filename
            ]);
        }
    }

    private function exportProfitsReport(Carbon $startDate, Carbon $endDate): void
    {
        // Obtener datos de ventas
        $salesData = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select(
                DB::raw('DATE(order_datetime) as date'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Obtener datos de costos
        $costsData = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_datetime', [$startDate, $endDate])
                    ->where('billed', true);
            })
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(
                DB::raw('DATE(orders.order_datetime) as date'),
                DB::raw('SUM(order_details.quantity * products.current_cost) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Combinar datos
        $dates = array_unique(array_merge(
            $salesData->pluck('date')->toArray(),
            $costsData->pluck('date')->toArray()
        ));
        sort($dates);

        $reportData = [];
        $totalSales = 0;
        $totalCosts = 0;
        $totalProfit = 0;

        foreach ($dates as $date) {
            $sales = $salesData[$date]->total ?? 0;
            $costs = $costsData[$date]->cost ?? 0;
            $profit = $sales - $costs;
            $margin = $sales > 0 ? ($profit / $sales) * 100 : 0;

            $reportData[] = [
                'date' => $date,
                'sales' => $sales,
                'costs' => $costs,
                'profit' => $profit,
                'margin' => $margin,
            ];

            $totalSales += $sales;
            $totalCosts += $costs;
            $totalProfit += $profit;
        }

        $totalMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;

        // Generar el reporte según el formato seleccionado
        if ($this->format === 'pdf') {
            $pdf = PDF::loadView('reports.profits', [
                'reportData' => $reportData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSales' => $totalSales,
                'totalCosts' => $totalCosts,
                'totalProfit' => $totalProfit,
                'totalMargin' => $totalMargin,
            ]);

            $filename = 'ganancias_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';

            // Enviar directamente al navegador
            $this->dispatch('download-pdf', [
                'content' => base64_encode($pdf->output()),
                'filename' => $filename
            ]);
        } else {
            // Crear archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'Reporte de Ganancias');
            $sheet->setCellValue('A2', 'Período: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
            $sheet->setCellValue('A4', 'Fecha');
            $sheet->setCellValue('B4', 'Ventas (S/)');
            $sheet->setCellValue('C4', 'Costos (S/)');
            $sheet->setCellValue('D4', 'Ganancia (S/)');
            $sheet->setCellValue('E4', 'Margen (%)');

            // Datos
            $row = 5;
            foreach ($reportData as $data) {
                $sheet->setCellValue('A' . $row, Carbon::parse($data['date'])->format('d/m/Y'));
                $sheet->setCellValue('B' . $row, $data['sales']);
                $sheet->setCellValue('C' . $row, $data['costs']);
                $sheet->setCellValue('D' . $row, $data['profit']);
                $sheet->setCellValue('E' . $row, number_format($data['margin'], 2));
                $row++;
            }

            // Totales
            $sheet->setCellValue('A' . ($row + 1), 'TOTALES');
            $sheet->setCellValue('B' . ($row + 1), $totalSales);
            $sheet->setCellValue('C' . ($row + 1), $totalCosts);
            $sheet->setCellValue('D' . ($row + 1), $totalProfit);
            $sheet->setCellValue('E' . ($row + 1), number_format($totalMargin, 2));

            // Guardar archivo
            $filename = 'ganancias_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';

            // Guardar en memoria
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Enviar directamente al navegador
            $this->dispatch('download-excel', [
                'content' => base64_encode($content),
                'filename' => $filename
            ]);
        }
    }

    private function exportProductsReport(Carbon $startDate, Carbon $endDate): void
    {
        // Obtener datos de productos vendidos
        $productsData = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_datetime', [$startDate, $endDate])
                    ->where('billed', true);
            })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as total_sales'),
                DB::raw('AVG(unit_price) as average_price')
            )
            ->with('product:id,name,category_id,current_cost', 'product.category:id,name')
            ->groupBy('product_id')
            ->orderByDesc('quantity_sold')
            ->get();

        // Calcular ganancias
        $reportData = $productsData->map(function ($item) {
            $cost = $item->product->current_cost ?? 0;
            $totalCost = $cost * $item->quantity_sold;
            $profit = $item->total_sales - $totalCost;
            $margin = $item->total_sales > 0 ? ($profit / $item->total_sales) * 100 : 0;

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'category' => $item->product->category->name ?? 'Sin categoría',
                'quantity_sold' => $item->quantity_sold,
                'total_sales' => $item->total_sales,
                'average_price' => $item->average_price,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'margin' => $margin,
            ];
        });

        // Generar el reporte según el formato seleccionado
        if ($this->format === 'pdf') {
            $pdf = PDF::loadView('reports.products', [
                'reportData' => $reportData,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

            $filename = 'productos_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';

            // Enviar directamente al navegador
            $this->dispatch('download-pdf', [
                'content' => base64_encode($pdf->output()),
                'filename' => $filename
            ]);
        } else {
            // Crear archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'Reporte de Productos Vendidos');
            $sheet->setCellValue('A2', 'Período: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
            $sheet->setCellValue('A4', 'Producto');
            $sheet->setCellValue('B4', 'Categoría');
            $sheet->setCellValue('C4', 'Cantidad Vendida');
            $sheet->setCellValue('D4', 'Ventas Totales (S/)');
            $sheet->setCellValue('E4', 'Precio Promedio (S/)');
            $sheet->setCellValue('F4', 'Costo Total (S/)');
            $sheet->setCellValue('G4', 'Ganancia (S/)');
            $sheet->setCellValue('H4', 'Margen (%)');

            // Datos
            $row = 5;
            foreach ($reportData as $data) {
                $sheet->setCellValue('A' . $row, $data['product_name']);
                $sheet->setCellValue('B' . $row, $data['category']);
                $sheet->setCellValue('C' . $row, $data['quantity_sold']);
                $sheet->setCellValue('D' . $row, $data['total_sales']);
                $sheet->setCellValue('E' . $row, $data['average_price']);
                $sheet->setCellValue('F' . $row, $data['total_cost']);
                $sheet->setCellValue('G' . $row, $data['profit']);
                $sheet->setCellValue('H' . $row, number_format($data['margin'], 2));
                $row++;
            }

            // Guardar archivo
            $filename = 'productos_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';

            // Guardar en memoria
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Enviar directamente al navegador
            $this->dispatch('download-excel', [
                'content' => base64_encode($content),
                'filename' => $filename
            ]);
        }
    }

    private function exportServiceTypesReport(Carbon $startDate, Carbon $endDate): void
    {
        // Obtener ventas por tipo de servicio
        $serviceTypeData = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select(
                'service_type',
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(total) as average')
            )
            ->groupBy('service_type')
            ->get();

        // Mapear nombres de tipos de servicio
        $serviceTypeLabels = [
            'dine_in' => 'En Local',
            'takeout' => 'Para Llevar',
            'delivery' => 'Delivery',
        ];

        $reportData = $serviceTypeData->map(function ($item) use ($serviceTypeLabels) {
            return [
                'service_type' => $item->service_type,
                'service_type_name' => $serviceTypeLabels[$item->service_type] ?? $item->service_type,
                'total' => $item->total,
                'count' => $item->count,
                'average' => $item->average,
            ];
        });

        $totalSales = $reportData->sum('total');
        $totalOrders = $reportData->sum('count');

        // Generar el reporte según el formato seleccionado
        if ($this->format === 'pdf') {
            $pdf = PDF::loadView('reports.service_types', [
                'reportData' => $reportData,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
            ]);

            $filename = 'tipos_servicio_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';

            // Enviar directamente al navegador
            $this->dispatch('download-pdf', [
                'content' => base64_encode($pdf->output()),
                'filename' => $filename
            ]);
        } else {
            // Crear archivo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $sheet->setCellValue('A1', 'Reporte de Ventas por Tipo de Servicio');
            $sheet->setCellValue('A2', 'Período: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
            $sheet->setCellValue('A4', 'Tipo de Servicio');
            $sheet->setCellValue('B4', 'Total Ventas (S/)');
            $sheet->setCellValue('C4', 'Cantidad de Órdenes');
            $sheet->setCellValue('D4', 'Ticket Promedio (S/)');
            $sheet->setCellValue('E4', 'Porcentaje del Total (%)');

            // Datos
            $row = 5;
            foreach ($reportData as $data) {
                $percentage = $totalSales > 0 ? ($data['total'] / $totalSales) * 100 : 0;

                $sheet->setCellValue('A' . $row, $data['service_type_name']);
                $sheet->setCellValue('B' . $row, $data['total']);
                $sheet->setCellValue('C' . $row, $data['count']);
                $sheet->setCellValue('D' . $row, $data['average']);
                $sheet->setCellValue('E' . $row, number_format($percentage, 2));
                $row++;
            }

            // Totales
            $sheet->setCellValue('A' . ($row + 1), 'TOTALES');
            $sheet->setCellValue('B' . ($row + 1), $totalSales);
            $sheet->setCellValue('C' . ($row + 1), $totalOrders);
            $sheet->setCellValue('E' . ($row + 1), '100.00');

            // Guardar archivo
            $filename = 'tipos_servicio_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';

            // Guardar en memoria
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Enviar directamente al navegador
            $this->dispatch('download-excel', [
                'content' => base64_encode($content),
                'filename' => $filename
            ]);
        }
    }

    public static function canView(): bool
    {
        // No mostrar este widget en ninguna página
        return false;
    }
}
