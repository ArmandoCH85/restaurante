<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Facades\DB;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // 🎯 TÍTULO DEL DASHBOARD
    protected static ?string $title = '📊 Dashboard de Ventas';

    // 🎨 GRID RESPONSIVO SEGÚN MEJORES PRÁCTICAS FILAMENT
    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,  // Móvil: 1 columna
            'sm' => 2,       // Tablet: 2 columnas
            'md' => 3,       // Desktop pequeño: 3 columnas
            'lg' => 4,       // Desktop: 4 columnas (REQUERIMIENTO ESPECÍFICO)
            'xl' => 4,       // Desktop grande: 4 columnas
            '2xl' => 4,      // Desktop extra: 4 columnas
        ];
    }

    // 📊 WIDGETS OPTIMIZADOS CON COLUMNSPAN RESPONSIVO
    protected function getHeaderWidgets(): array
    {
        return [
            // 📊 ESTADÍSTICAS PRINCIPALES - ANCHO COMPLETO SIEMPRE
            \App\Filament\Widgets\SalesStatsWidget::class,

            // 📈 GRÁFICO TENDENCIAS - ANCHO COMPLETO
            \App\Filament\Widgets\SalesChartWidget::class,

            // 🏆 TOP PRODUCTOS - ANCHO COMPLETO PARA TABLA
            \App\Filament\Widgets\TopProductsWidget::class,

            // 💳 MÉTODOS DE PAGO Y ⏰ HORAS PICO - LADO A LADO EN DESKTOP
            \App\Filament\Widgets\PaymentMethodsWidget::class,
            \App\Filament\Widgets\SalesHoursWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // 📊 BOTONES DE EXPORTACIÓN - OPTIMIZADOS VISUALMENTE
            Actions\Action::make('exportPDF')
                ->label('📄 PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->tooltip('Exportar dashboard completo a PDF')
                ->extraAttributes(['class' => 'transition-all duration-200 hover:scale-105'])
                ->form([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('📅 Fecha Inicio')
                                ->default(now()->startOfMonth())
                                ->required()
                                ->native(false),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('📅 Fecha Fin')
                                ->default(now())
                                ->required()
                                ->native(false),
                        ]),
                    Forms\Components\Select::make('service_type')
                        ->label('🍽️ Tipo de Servicio')
                        ->options([
                            'all' => '🍽️ Todos los servicios',
                            'mesa' => '🍽️ Solo Mesa',
                            'delivery' => '🚚 Solo Delivery',
                            'directa' => '🥡 Solo Venta Directa',
                        ])
                        ->default('all')
                        ->native(false),
                ])
                ->action(function (array $data) {
                    return $this->exportDashboardPDF($data);
                })
                ->modalHeading('📄 Exportar Dashboard a PDF')
                ->modalDescription('Genera un reporte completo con estadísticas visuales')
                ->modalSubmitActionLabel('📥 Generar PDF')
                ->modalIcon('heroicon-o-document-arrow-down'),

            Actions\Action::make('exportExcel')
                ->label('📊 Excel')
                ->color('success')
                ->icon('heroicon-o-table-cells')
                ->tooltip('Exportar datos a Excel con múltiples hojas')
                ->extraAttributes(['class' => 'transition-all duration-200 hover:scale-105'])
                ->form([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('📅 Fecha Inicio')
                                ->default(now()->startOfMonth())
                                ->required()
                                ->native(false),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('📅 Fecha Fin')
                                ->default(now())
                                ->required()
                                ->native(false),
                        ]),
                    Forms\Components\Select::make('service_type')
                        ->label('🍽️ Tipo de Servicio')
                        ->options([
                            'all' => '🍽️ Todos los servicios',
                            'mesa' => '🍽️ Solo Mesa',
                            'delivery' => '🚚 Solo Delivery',
                            'directa' => '🥡 Solo Venta Directa',
                        ])
                        ->default('all')
                        ->native(false),
                    Forms\Components\Section::make('📂 Secciones a Incluir')
                        ->description('Selecciona qué datos incluir en el archivo Excel')
                        ->schema([
                            Forms\Components\CheckboxList::make('sections')
                                ->options([
                                    'stats' => '📊 Estadísticas Generales',
                                    'sales_trend' => '📈 Tendencia de Ventas',
                                    'top_products' => '🏆 Productos Más Vendidos',
                                    'payment_methods' => '💳 Métodos de Pago',
                                    'sales_hours' => '⏰ Horas Pico',
                                    'orders_detail' => '📋 Detalle de Órdenes',
                                ])
                                ->default(['stats', 'sales_trend', 'top_products', 'payment_methods'])
                                ->columns(2)
                                ->bulkToggleable(),
                        ])
                        ->collapsible(),
                ])
                ->action(function (array $data) {
                    return $this->exportDashboardExcel($data);
                })
                ->modalHeading('📊 Exportar Dashboard a Excel')
                ->modalDescription('Crea un archivo Excel con múltiples hojas de datos')
                ->modalSubmitActionLabel('📥 Generar Excel')
                ->modalIcon('heroicon-o-table-cells')
                ->slideOver(),

            // 🎯 NAVEGACIÓN RÁPIDA - SEPARADOR VISUAL
            Actions\Action::make('divider_1')
                ->label('|')
                ->disabled()
                ->color('gray'),

            // 🚀 BOTONES DE NAVEGACIÓN RÁPIDA - ESTILO MEJORADO
            Actions\Action::make('go_to_pos')
                ->label('🚀 POS')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->tooltip('Ir al sistema POS para nueva venta')
                ->url('/admin/pos-interface')
                ->extraAttributes([
                    'class' => 'transition-all duration-200 hover:scale-105 hover:shadow-lg'
                ]),

            Actions\Action::make('go_to_tables')
                ->label('🍽️ Mesas')
                ->icon('heroicon-o-map')
                ->color('info')
                ->tooltip('Ver mapa de mesas del restaurante')
                ->url('/admin/mapa-mesas')
                ->extraAttributes([
                    'class' => 'transition-all duration-200 hover:scale-105 hover:shadow-lg'
                ]),
        ];
    }

    // 🎨 PERSONALIZACIÓN DEL LAYOUT SEGÚN FILAMENT DOCS
    public function getTitle(): string
    {
        return '📊 Dashboard de Ventas';
    }

    public function getSubheading(): ?string
    {
        $currentDate = now()->format('d/m/Y H:i');
        return "📅 Actualizado: {$currentDate} | 🔄 Datos en tiempo real | 🚀 Para nuevas ventas: POS o Mapa de Mesas";
    }

    // 🎯 BREADCRUMBS PERSONALIZADOS
    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => '🏠 Inicio',
            '/admin/reportes' => '📊 Reportes',
            '' => '📈 Dashboard Ventas',
        ];
    }

    // ⚡ OPTIMIZACIÓN DE POLLING PARA WIDGETS
    protected static ?string $pollingInterval = '30s'; // Actualización cada 30 segundos

    // 🎨 INYECCIÓN DE ESTILOS MEJORADOS EN LA VISTA
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'customCss' => $this->getCustomCss(),
        ]);
    }

    // 🎨 CSS PERSONALIZADO OPTIMIZADO PARA FILAMENT
    private function getCustomCss(): string
    {
        return "
            <link rel=\"stylesheet\" href=\"" . asset('css/dashboard-widgets.css') . "\">
            <style>
                /* 🎯 ESTILOS ESPECÍFICOS PARA DASHBOARD DE VENTAS */
                .fi-header-heading {
                    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                    font-weight: 800;
                }

                .fi-header-subheading {
                    color: #6b7280;
                    font-weight: 500;
                }

                /* 📊 CONTENEDOR DE WIDGETS OPTIMIZADO */
                .fi-section-content-ctn {
                    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                    border-radius: 1rem;
                    padding: 1.5rem;
                }

                /* 🚀 BOTONES DE ACCIÓN MEJORADOS */
                .fi-btn {
                    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                    font-weight: 600;
                }

                .fi-btn:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }

                /* 📱 RESPONSIVE OPTIMIZATIONS */
                @media (max-width: 768px) {
                    .fi-section-content-ctn {
                        padding: 1rem;
                        margin: 0.5rem;
                    }
                }
            </style>
        ";
    }

    // 📄 EXPORTACIÓN A PDF
    public function exportDashboardPDF(array $data)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $serviceType = $data['service_type'];

        // 📊 OBTENER DATOS DEL DASHBOARD
        $dashboardData = $this->getDashboardData($startDate, $endDate, $serviceType);

        // 🎨 GENERAR PDF
        $pdf = Pdf::loadView('exports.dashboard-pdf', [
            'data' => $dashboardData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'serviceType' => $serviceType,
        ]);

        $filename = 'dashboard_ventas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    // 📊 EXPORTACIÓN A EXCEL
    public function exportDashboardExcel(array $data)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $serviceType = $data['service_type'];
        $sections = $data['sections'] ?? ['stats', 'sales_trend', 'top_products', 'payment_methods'];

        // 📊 OBTENER DATOS DEL DASHBOARD
        $dashboardData = $this->getDashboardData($startDate, $endDate, $serviceType);

        // 📑 CREAR EXCEL
        $spreadsheet = new Spreadsheet();
        $this->createExcelSheets($spreadsheet, $dashboardData, $sections, $startDate, $endDate, $serviceType);

        $filename = 'dashboard_ventas_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename);
    }

    // 📊 OBTENER DATOS CONSOLIDADOS DEL DASHBOARD
    private function getDashboardData(Carbon $startDate, Carbon $endDate, string $serviceType): array
    {
        // 🔍 QUERY BASE CON FILTROS
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('billed', true);

        // 🏷️ FILTRO POR TIPO DE SERVICIO
        if ($serviceType !== 'all') {
            switch ($serviceType) {
                case 'mesa':
                    $ordersQuery->whereNotNull('table_id');
                    break;
                case 'delivery':
                    $ordersQuery->whereHas('deliveryOrder');
                    break;
                case 'directa':
                    $ordersQuery->whereNull('table_id')->whereDoesntHave('deliveryOrder');
                    break;
            }
        }

        $orders = $ordersQuery->with(['orderDetails.product', 'payments'])->get();

        // 📊 ESTADÍSTICAS GENERALES
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $averageTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // 🍽️ VENTAS POR TIPO
        $salesByType = [
            'mesa' => $orders->whereNotNull('table_id')->sum('total'),
            'delivery' => $orders->filter(fn($order) => $order->deliveryOrder)->sum('total'),
            'directa' => $orders->filter(fn($order) => is_null($order->table_id) && !$order->deliveryOrder)->sum('total'),
        ];

        // 🏆 PRODUCTOS MÁS VENDIDOS
        $topProducts = OrderDetail::whereIn('order_id', $orders->pluck('id'))
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // 💳 MÉTODOS DE PAGO
        $paymentMethods = Payment::whereIn('order_id', $orders->pluck('id'))
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($payment) {
                $method = match($payment->payment_method) {
                    'cash' => '💵 Efectivo',
                    'credit_card', 'debit_card', 'card' => '💳 Tarjetas',
                    'yape' => '📱 Yape',
                    'plin' => '💙 Plin',
                    'bank_transfer', 'transfer' => '🏦 Transferencias',
                    default => $payment->payment_method,
                };
                return [$method => $payment->total_amount];
            });

        // ⏰ VENTAS POR HORA
        $salesByHour = $orders->groupBy(function ($order) {
            return $order->created_at->format('H:00');
        })->map(function ($hourOrders) {
            return $hourOrders->sum('total');
        })->sortKeys();

        return [
            'stats' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'average_ticket' => $averageTicket,
                'sales_by_type' => $salesByType,
            ],
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
            'sales_by_hour' => $salesByHour,
            'orders' => $orders,
        ];
    }

    // 📑 CREAR HOJAS DE EXCEL
    private function createExcelSheets(Spreadsheet $spreadsheet, array $data, array $sections, Carbon $startDate, Carbon $endDate, string $serviceType): void
    {
        $spreadsheet->removeSheetByIndex(0); // Eliminar hoja por defecto

        foreach ($sections as $section) {
            switch ($section) {
                case 'stats':
                    $this->createStatsSheet($spreadsheet, $data['stats'], $startDate, $endDate, $serviceType);
                    break;
                case 'top_products':
                    $this->createTopProductsSheet($spreadsheet, $data['top_products']);
                    break;
                case 'payment_methods':
                    $this->createPaymentMethodsSheet($spreadsheet, $data['payment_methods']);
                    break;
                case 'sales_hours':
                    $this->createSalesHoursSheet($spreadsheet, $data['sales_by_hour']);
                    break;
                case 'orders_detail':
                    $this->createOrdersSheet($spreadsheet, $data['orders']);
                    break;
            }
        }

        // Activar la primera hoja
        $spreadsheet->setActiveSheetIndex(0);
    }

    // 📊 HOJA DE ESTADÍSTICAS
    private function createStatsSheet(Spreadsheet $spreadsheet, array $stats, Carbon $startDate, Carbon $endDate, string $serviceType): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('📊 Estadísticas');

        // Encabezados
        $sheet->setCellValue('A1', 'DASHBOARD DE VENTAS - ESTADÍSTICAS GENERALES');
        $sheet->setCellValue('A2', 'Período: ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
        $sheet->setCellValue('A3', 'Tipo de Servicio: ' . ucfirst($serviceType));

        // Estadísticas principales
        $sheet->setCellValue('A5', 'RESUMEN GENERAL');
        $sheet->setCellValue('A6', 'Total Ventas:');
        $sheet->setCellValue('B6', 'S/ ' . number_format($stats['total_sales'], 2));
        $sheet->setCellValue('A7', 'Total Órdenes:');
        $sheet->setCellValue('B7', $stats['total_orders']);
        $sheet->setCellValue('A8', 'Ticket Promedio:');
        $sheet->setCellValue('B8', 'S/ ' . number_format($stats['average_ticket'], 2));

        // Ventas por tipo
        $sheet->setCellValue('A10', 'VENTAS POR TIPO DE SERVICIO');
        $row = 11;
        foreach ($stats['sales_by_type'] as $type => $amount) {
            $typeLabel = match($type) {
                'mesa' => '🍽️ Mesa',
                'delivery' => '🚚 Delivery',
                'directa' => '🥡 Venta Directa',
                default => $type,
            };
            $sheet->setCellValue('A' . $row, $typeLabel . ':');
            $sheet->setCellValue('B' . $row, 'S/ ' . number_format($amount, 2));
            $row++;
        }

        // Aplicar estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A5:A10')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
    }

    // 🏆 HOJA DE PRODUCTOS TOP
    private function createTopProductsSheet(Spreadsheet $spreadsheet, $topProducts): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('🏆 Top Productos');

        // Encabezados
        $sheet->setCellValue('A1', 'PRODUCTOS MÁS VENDIDOS');
        $sheet->setCellValue('A3', 'Ranking');
        $sheet->setCellValue('B3', 'Producto');
        $sheet->setCellValue('C3', 'Cantidad Vendida');
        $sheet->setCellValue('D3', 'Ingresos Totales');

        // Datos
        $row = 4;
        foreach ($topProducts as $index => $product) {
            $ranking = $index + 1;
            $medal = match($ranking) {
                1 => '🥇',
                2 => '🥈',
                3 => '🥉',
                default => $ranking,
            };

            $sheet->setCellValue('A' . $row, $medal);
            $sheet->setCellValue('B' . $row, $product->product->name ?? 'Producto eliminado');
            $sheet->setCellValue('C' . $row, $product->total_quantity);
            $sheet->setCellValue('D' . $row, 'S/ ' . number_format($product->total_revenue, 2));
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3:D3')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
    }

    // 💳 HOJA DE MÉTODOS DE PAGO
    private function createPaymentMethodsSheet(Spreadsheet $spreadsheet, $paymentMethods): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('💳 Métodos de Pago');

        // Encabezados
        $sheet->setCellValue('A1', 'DISTRIBUCIÓN DE MÉTODOS DE PAGO');
        $sheet->setCellValue('A3', 'Método de Pago');
        $sheet->setCellValue('B3', 'Monto Total');
        $sheet->setCellValue('C3', 'Porcentaje');

        $totalAmount = $paymentMethods->sum();

        // Datos
        $row = 4;
        foreach ($paymentMethods as $method => $amount) {
            $percentage = $totalAmount > 0 ? ($amount / $totalAmount) * 100 : 0;

            $sheet->setCellValue('A' . $row, $method);
            $sheet->setCellValue('B' . $row, 'S/ ' . number_format($amount, 2));
            $sheet->setCellValue('C' . $row, number_format($percentage, 1) . '%');
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
    }

    // ⏰ HOJA DE HORAS PICO
    private function createSalesHoursSheet(Spreadsheet $spreadsheet, $salesByHour): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('⏰ Horas Pico');

        // Encabezados
        $sheet->setCellValue('A1', 'VENTAS POR HORA DEL DÍA');
        $sheet->setCellValue('A3', 'Hora');
        $sheet->setCellValue('B3', 'Ventas Totales');

        // Datos
        $row = 4;
        foreach ($salesByHour as $hour => $amount) {
            $sheet->setCellValue('A' . $row, $hour);
            $sheet->setCellValue('B' . $row, 'S/ ' . number_format($amount, 2));
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3:B3')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
    }

    // 📋 HOJA DE ÓRDENES DETALLADAS
    private function createOrdersSheet(Spreadsheet $spreadsheet, $orders): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('📋 Detalle Órdenes');

        // Encabezados
        $sheet->setCellValue('A1', 'DETALLE DE ÓRDENES');
        $sheet->setCellValue('A3', 'Orden ID');
        $sheet->setCellValue('B3', 'Fecha');
        $sheet->setCellValue('C3', 'Mesa');
        $sheet->setCellValue('D3', 'Tipo Servicio');
        $sheet->setCellValue('E3', 'Total');
        $sheet->setCellValue('F3', 'Estado');

        // Datos
        $row = 4;
        foreach ($orders as $order) {
            $serviceType = $order->table_id ? '🍽️ Mesa' : ($order->deliveryOrder ? '🚚 Delivery' : '🥡 Directa');

            $sheet->setCellValue('A' . $row, $order->id);
            $sheet->setCellValue('B' . $row, $order->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('C' . $row, $order->table->number ?? 'N/A');
            $sheet->setCellValue('D' . $row, $serviceType);
            $sheet->setCellValue('E' . $row, 'S/ ' . number_format($order->total, 2));
            $sheet->setCellValue('F' . $row, $order->billed ? '✅ Facturado' : '⏳ Pendiente');
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
    }
}
