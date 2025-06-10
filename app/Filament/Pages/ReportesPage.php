<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Filament\Support\Exceptions\Halt;

class ReportesPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title = 'Reportes';
    protected static ?string $navigationGroup = '📊 Reportes y Análisis';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'reportes';

    protected static string $view = 'filament.pages.reportes-page';

    protected ?string $heading = 'Reportes';

    // Propiedades del formulario
    public ?string $reportType = 'sales';
    public ?string $dateRange = 'today';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $format = 'pdf';

    public function mount(): void
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');

        // Establecer valores iniciales
        $this->form->fill([
            'reportType' => 'sales',
            'dateRange' => 'today',
            'format' => 'pdf',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->default('today')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if ($state !== 'custom') {
                            $this->startDate = match ($state) {
                                'today' => Carbon::today()->format('Y-m-d'),
                                'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                                'week' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                                'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                                default => Carbon::today()->format('Y-m-d'),
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
                    ->default(Carbon::today())
                    ->visible(fn ($get) => $get('dateRange') === 'custom')
                    ->required(),

                DatePicker::make('endDate')
                    ->label('Fecha Fin')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->default(Carbon::today())
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
            ]);
    }

    public function exportReport()
    {
        // Obtener datos del formulario
        $formData = $this->form->getState();

        // Establecer valores
        $this->format = $formData['format'] ?? 'pdf';
        $this->reportType = $formData['reportType'] ?? 'sales';
        $this->dateRange = $formData['dateRange'] ?? 'today';

        // Si es un rango personalizado, usar las fechas del formulario
        if ($this->dateRange === 'custom') {
            $this->startDate = $formData['startDate'] ?? Carbon::today()->format('Y-m-d');
            $this->endDate = $formData['endDate'] ?? Carbon::today()->format('Y-m-d');
        } else {
            // Establecer fechas según el rango seleccionado
            $this->startDate = match ($this->dateRange) {
                'today' => Carbon::today()->format('Y-m-d'),
                'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                'week' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                default => Carbon::today()->format('Y-m-d'),
            };

            $this->endDate = match ($this->dateRange) {
                'today' => Carbon::today()->format('Y-m-d'),
                'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                'week' => Carbon::now()->endOfWeek()->format('Y-m-d'),
                'month' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                default => Carbon::today()->format('Y-m-d'),
            };
        }

        // Convertir fechas a objetos Carbon
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        try {
            // Generar el reporte según el tipo seleccionado
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

            // Mostrar notificación usando la API de Filament
            Notification::make()
                ->title('Reporte generado correctamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Registrar el error para depuración
            \Log::error('Error al generar reporte: ' . $e->getMessage(), [
                'exception' => $e,
                'reportType' => $this->reportType,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'format' => $this->format
            ]);

            Notification::make()
                ->title('Error al generar el reporte')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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

            // Generar el contenido del PDF
            $pdfContent = $pdf->output();

            // Verificar que el contenido no esté vacío
            if (empty($pdfContent)) {
                throw new \Exception('El contenido del PDF está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-pdf',
                content: base64_encode($pdfContent),
                filename: $filename,
                id: uniqid('pdf_')
            );
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

            // Guardar en memoria
            $filename = 'ventas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Verificar que el contenido no esté vacío
            if (empty($content)) {
                throw new \Exception('El contenido del Excel está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-excel',
                content: base64_encode($content),
                filename: $filename,
                id: uniqid('excel_')
            );
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

            // Generar el contenido del PDF
            $pdfContent = $pdf->output();

            // Verificar que el contenido no esté vacío
            if (empty($pdfContent)) {
                throw new \Exception('El contenido del PDF está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-pdf',
                content: base64_encode($pdfContent),
                filename: $filename,
                id: uniqid('pdf_')
            );
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

            // Guardar en memoria
            $filename = 'productos_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Verificar que el contenido no esté vacío
            if (empty($content)) {
                throw new \Exception('El contenido del Excel está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-excel',
                content: base64_encode($content),
                filename: $filename,
                id: uniqid('excel_')
            );
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

            // Generar el contenido del PDF
            $pdfContent = $pdf->output();

            // Verificar que el contenido no esté vacío
            if (empty($pdfContent)) {
                throw new \Exception('El contenido del PDF está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-pdf',
                content: base64_encode($pdfContent),
                filename: $filename,
                id: uniqid('pdf_')
            );
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

            // Guardar en memoria
            $filename = 'tipos_servicio_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Verificar que el contenido no esté vacío
            if (empty($content)) {
                throw new \Exception('El contenido del Excel está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-excel',
                content: base64_encode($content),
                filename: $filename,
                id: uniqid('excel_')
            );
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

            // Generar el contenido del PDF
            $pdfContent = $pdf->output();

            // Verificar que el contenido no esté vacío
            if (empty($pdfContent)) {
                throw new \Exception('El contenido del PDF está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-pdf',
                content: base64_encode($pdfContent),
                filename: $filename,
                id: uniqid('pdf_')
            );
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

            // Guardar en memoria
            $filename = 'ganancias_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            // Verificar que el contenido no esté vacío
            if (empty($content)) {
                throw new \Exception('El contenido del Excel está vacío');
            }

            // Enviar directamente al navegador con un ID único para depuración
            $this->dispatch('download-excel',
                content: base64_encode($content),
                filename: $filename,
                id: uniqid('excel_')
            );
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
