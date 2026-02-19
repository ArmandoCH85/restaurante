<?php
/*
 * PÁGINA: Visualizador Individual de Reportes
 *
 * Esta clase implementa la página de visualización detallada para cada tipo de reporte
 * con filtros avanzados, exportación a Excel y navegación optimizada.
 *
 * CAMBIOS RECIENTES:
 * - Se implementó sistema de filtrado avanzado por fechas y horas
 * - Se agregó soporte para diferentes tipos de comprobantes en reportes de contabilidad
 * - Se optimizó la exportación a Excel con formato mejorado
 * - Se implementaron métodos específicos para cada tipo de reporte
 * - Se mejoró la gestión de canales de venta para reportes de productos
 * - Se agregó logging detallado para depuración de consultas
 */

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsByChannelExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Filament\Pages\ReportViewerPageLog;
use App\Services\ExcelExportLogger;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportViewerPage extends Page implements HasForms
{
    use InteractsWithForms;
    
    // protected static string $layout = 'filament-panels::layout.index';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'report-viewer/{category}/{reportType}';
    protected static string $view = 'filament.pages.report-viewer-page';
    protected ?string $maxContentWidth = 'full';
    
    protected function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'download-excel-file' => 'downloadExcelFile',
        ]);
    }
    
    public function downloadExcelFile(string $url): void
    {
        $this->dispatch('download-file', url: $url);
    }

    // Método para verificar si el filtro de canal debe ser visible
    public function shouldShowChannelFilter(): bool
    {
        return $this->reportType === 'products_by_channel';
    }

    // URL parameters
    public string $reportType = '';
    public string $category = '';

    // Form properties
    public ?string $dateRange = 'today';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $startTime = null;
    public ?string $endTime = null;
    public ?string $format = 'pdf';
    public ?string $invoiceType = null;
    public ?string $channelFilter = null;

    // Data properties
    public $reportData;
    public $reportStats = [];
    
    public function __construct()
    {
        // Inicializar reportData como colección vacía
        $this->reportData = collect([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('← Volver a Reportes')
                ->color('gray')
                ->url(route('filament.admin.pages.reportes'))
                ->icon('heroicon-o-arrow-left'),
                
            Action::make('downloadExcel')
                ->label('📊 Descargar Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    ReportViewerPageLog::writeRaw("DESCARGA DE EXCEL DESDE ACCIÓN\n");
                    
                    // Cargar los datos si no están cargados
                    if ($this->reportData->isEmpty()) {
                        $this->loadReportData();
                    }
                    
                    $response = $this->exportReport();
                    if ($response) {
                        return $response;
                    } else {
                        Notification::make()
                            ->title('❌ Error al generar Excel')
                            ->body('No se pudo generar el archivo')
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => !request()->has('download')),
        ];
    }
    

    public function mount(string $category = '', string $reportType = ''): void
    {
        $this->category = $category;
        $this->reportType = $reportType;
        
        // DEBUG: Agregar logging para verificar valores
        ReportViewerPageLog::writeRaw("\n=== DEBUG MOUNT ===\n");
        ReportViewerPageLog::writeRaw("Category: " . $category . "\n");
        ReportViewerPageLog::writeRaw("ReportType: " . $reportType . "\n");
        ReportViewerPageLog::writeRaw("shouldShowChannelFilter(): " . ($this->shouldShowChannelFilter() ? 'true' : 'false') . "\n");
        
        // Verificar si hay solicitud de descarga de Excel
        $request = request();
        
        // Escribir en el log individual usando la nueva clase
        ReportViewerPageLog::writeRaw("\n=== INICIO MOUNT ===\n");
        ReportViewerPageLog::writeRaw("Fecha/Hora: " . date('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("Categoría recibida: " . $category . "\n");
        ReportViewerPageLog::writeRaw("Tipo de reporte recibido: " . $reportType . "\n");
        ReportViewerPageLog::writeRaw("Parámetros GET: " . json_encode($request->all()) . "\n");
        
        // Manejar descarga de Excel para cualquier tipo de reporte
        if ($request->input('download') === 'excel') {
            ReportViewerPageLog::writeRaw("DESCARGA DE EXCEL DETECTADA\n");
            ReportViewerPageLog::writeRaw("Tipo de reporte para exportar: " . $this->reportType . "\n");
            
            try {
                // Para ventas y contabilidad, usar la nueva ruta de descarga directa
                if (($this->category === 'sales' && $this->reportType === 'all_sales') ||
                    ($this->category === 'finance' && $this->reportType === 'accounting_reports')) {
                    ReportViewerPageLog::writeRaw("Usando descarga directa con JavaScript...\n");
                    
                    $params = [
                        'startDate' => $request->input('startDate', now()->format('Y-m-d')),
                        'endDate' => $request->input('endDate', now()->format('Y-m-d'))
                    ];
                    
                    if ($request->has('invoiceType')) {
                        $params['invoiceType'] = $request->input('invoiceType');
                    }
                    
                    if ($request->has('channelFilter')) {
                        $params['channelFilter'] = $request->input('channelFilter');
                    }
                    
                    // Crear URL de descarga directa según el tipo de reporte (Principio KISS)
                    $downloadUrl = match($this->reportType) {
                        'all_sales' => route('admin.reportes.sales.excel', $params),
                        'products_by_channel' => route('admin.reportes.products-by-channel.excel', $params),
                        'all_purchases' => route('admin.reportes.purchases.excel', $params),
                        'payment_methods' => route('admin.reportes.payment-methods.excel', $params),
                        'cash_register' => route('admin.reportes.cash-register.excel', $params),
                        'accounting_reports' => route('admin.reportes.accounting.excel', $params),
                        default => route('sales.excel.download', $params) // Fallback para compatibilidad
                    };
                    
                    ReportViewerPageLog::writeRaw("URL de descarga: " . $downloadUrl . "\n");
                    
                    // Usar JavaScript para forzar la descarga inmediatamente
                    $this->dispatch('download-excel-file', url: $downloadUrl);
                    
                    // Mostrar notificación
                    Notification::make()
                        ->title('Descarga iniciada')
                        ->body('El archivo Excel se está descargando automáticamente')
                        ->success()
                        ->send();
                    
                    return;
                }
                
                // Para otros tipos de reportes, usar el método tradicional
                ReportViewerPageLog::writeRaw("Cargando datos del reporte...\n");
                $this->loadReportData();
                
                ReportViewerPageLog::writeRaw("Datos cargados exitosamente\n");
                ReportViewerPageLog::writeRaw("Total de registros: " . $this->reportData->count() . "\n");
                
                // Llamar al método exportReport que ahora maneja todos los tipos
                ReportViewerPageLog::writeRaw("Ejecutando exportReport()...\n");
                $response = $this->exportReport();
                
                if ($response) {
                    ReportViewerPageLog::writeRaw("Excel generado exitosamente\n");
                    // No podemos retornar aquí porque mount() es void
                } else {
                    ReportViewerPageLog::writeRaw("exportReport() retornó null\n");
                }
                
            } catch (\Exception $e) {
                ReportViewerPageLog::writeRaw("ERROR EN MOUNT: " . $e->getMessage() . "\n");
                ReportViewerPageLog::writeRaw("Archivo: " . $e->getFile() . "\n");
                ReportViewerPageLog::writeRaw("Línea: " . $e->getLine() . "\n");
                ReportViewerPageLog::writeRaw("Trace: " . $e->getTraceAsString() . "\n");
                
                Notification::make()
                    ->title('Error al exportar')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
                    
                return;
            }
        }
        
        // Only proceed if we have valid parameters
        if (!empty($this->category) && !empty($this->reportType)) {
            // Capturar filtro de tipo de comprobante del request
            $this->invoiceType = request('invoiceType');
            // Capturar filtro de canal de venta del request
            $this->channelFilter = request('channelFilter');
            // Capturar fechas desde los parámetros de la URL
            if (request('startDate')) {
                $this->startDate = request('startDate');
            }
            if (request('endDate')) {
                $this->endDate = request('endDate');
            }
            // Capturar horas desde los parámetros de la URL
            if (request('startTime')) {
                $this->startTime = request('startTime');
            }
            if (request('endTime')) {
                $this->endTime = request('endTime');
            }
            
            // Log de depuración MUY DETALLADO para verificar valores recibidos
            ReportViewerPageLog::writeRaw("\n" . str_repeat("=", 80) . "\n");
            ReportViewerPageLog::writeRaw("=== MOUNT() - INICIO DE CAPTURA DE PARÁMETROS ===\n");
            ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
            ReportViewerPageLog::writeRaw("URL COMPLETA: " . request()->fullUrl() . "\n");
            ReportViewerPageLog::writeRaw("MÉTODO HTTP: " . request()->method() . "\n");
            ReportViewerPageLog::writeRaw("QUERY STRING: " . request()->getQueryString() . "\n");
            ReportViewerPageLog::writeRaw("=== TODOS LOS PARÁMETROS DEL REQUEST ===\n");
            $allParams = request()->all();
            foreach ($allParams as $key => $value) {
                ReportViewerPageLog::writeRaw("  - $key: " . (is_array($value) ? json_encode($value) : $value) . "\n");
            }
            
            ReportViewerPageLog::writeRaw("=== VALORES ASIGNADOS A PROPIEDADES ===\n");
            ReportViewerPageLog::writeRaw("  invoiceType (antes de asignar): " . ($this->invoiceType ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("  channelFilter (antes de asignar): " . ($this->channelFilter ?? 'null') . "\n");
            
            // Capturar valores con request() explícitamente
            $this->invoiceType = request('invoiceType');
            $this->channelFilter = request('channelFilter');
            
            ReportViewerPageLog::writeRaw("  invoiceType (después de asignar): " . ($this->invoiceType ?? 'null') . " (tipo: " . gettype($this->invoiceType) . ")\n");
            ReportViewerPageLog::writeRaw("  channelFilter (después de asignar): " . ($this->channelFilter ?? 'null') . " (tipo: " . gettype($this->channelFilter) . ")\n");
            
            ReportViewerPageLog::writeRaw("=== ESTADO ACTUAL DEL COMPONENTE ===\n");
            ReportViewerPageLog::writeRaw("  category: " . ($this->category ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("  reportType: " . ($this->reportType ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("  dateRange: " . ($this->dateRange ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("  startDate: " . ($this->startDate ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("  endDate: " . ($this->endDate ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("=== MOUNT() - FIN DE CAPTURA ===\n");
            
            // Si no viene dateRange en la URL, NO aplicar filtros por defecto
            if (request()->has('dateRange')) {
                $dateRange = request('dateRange');
                $this->setDateRange($dateRange);
            } else {
                // Para este reporte, aplicar "hoy" por defecto.
                if ($this->reportType === 'products_by_channel') {
                    $this->setDateRange('today');
                } else {
                    // Cargar SIN FILTROS - todos los datos históricos
                    $this->dateRange = null;
                    $this->startDate = null;
                    $this->endDate = null;
                }
            }
            
            // Initialize form
            $this->form->fill([
                'dateRange' => $this->dateRange,
                'format' => 'pdf',
            ]);

            // Load initial data (libre de filtros por defecto)
            $this->loadReportData();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('dateRange')
                    ->label('Período de Tiempo')
                    ->options([
                        'today' => '📅 Hoy',
                        'yesterday' => '📅 Ayer',
                        'week' => '📅 Esta semana',
                        'month' => '📅 Este mes',
                        'custom' => '📅 Personalizado',
                    ])
                    ->default('today')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->setDateRange($state);
                    }),

                DatePicker::make('startDate')
                    ->label('📅 Fecha Inicio')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->default(now()->startOfDay())
                    ->visible(fn ($get) => $get('dateRange') === 'custom'),

                DatePicker::make('endDate')
                    ->label('📅 Fecha Fin')
                    ->format('Y-m-d')
                    ->displayFormat('d/m/Y')
                    ->default(now()->endOfDay())
                    ->visible(fn ($get) => $get('dateRange') === 'custom'),

                TimePicker::make('startTime')
                    ->label('🕐 Hora Inicio (Opcional)')
                    ->displayFormat('H:i')
                    ->format('H:i'),

                TimePicker::make('endTime')
                    ->label('🕐 Hora Fin (Opcional)')
                    ->displayFormat('H:i')
                    ->format('H:i'),

                Select::make('format')
                    ->label('Formato de Exportación')
                    ->options([
                        'pdf' => '📄 PDF',
                        'excel' => '📊 Excel',
                    ])
                    ->default('pdf'),

                Select::make('invoiceType')
                    ->label('📄 Tipo Comprobante (opcional)')
                    ->options([
                        '' => 'Todos los tipos',
                        'receipt' => '🧾 Boleta',
                        'invoice' => '📋 Factura',
                    ])
                    ->default(fn() => request('invoiceType') ?? '')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->invoiceType = $state;
                        $this->loadReportData();
                    }),

                Select::make('channelFilter')
                    ->label('🛒 Canal de Venta (opcional)')
                    ->options([
                        '' => 'Todos los canales',
                        'dine_in' => '🍽️ En Mesa',
                        'takeout' => '📦 Para Llevar',
                        'delivery' => '🚚 Delivery',
                        'drive_thru' => '🚗 Auto Servicio',
                    ])
                    ->default(fn() => request('channelFilter') ?? '')
                    ->visible(true) // Forzar visibilidad para debug
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->channelFilter = $state;
                        $this->loadReportData();
                    }),
            ])
            ->columns(3);
    }

    public function setDateRange(string $range): void
    {
        $this->dateRange = $range;
        
        if ($range !== 'custom') {
            $this->startDate = match ($range) {
                'today' => Carbon::today()->format('Y-m-d'),
                'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                'week' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                default => Carbon::today()->format('Y-m-d'),
            };

            $this->endDate = match ($range) {
                'today' => Carbon::today()->format('Y-m-d'),
                'yesterday' => Carbon::yesterday()->format('Y-m-d'),
                'week' => Carbon::now()->endOfWeek()->format('Y-m-d'),
                'month' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                default => Carbon::today()->format('Y-m-d'),
            };
        }
        
        $this->form->fill([
            'dateRange' => $this->dateRange,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
        
        $this->loadReportData();
    }

    protected function getStartDateTime(): Carbon
    {
        $date = Carbon::parse($this->startDate);
        
        if ($this->startTime) {
            // Si se especifica hora, usarla
            $timeParts = explode(':', $this->startTime);
            $date->setTime($timeParts[0], $timeParts[1] ?? 0, 0);
        } else {
            // Si no se especifica hora, comenzar desde las 00:00:00
            $date->startOfDay();
        }
        
        return $date;
    }

    protected function getEndDateTime(): Carbon
    {
        $date = Carbon::parse($this->endDate);
        
        if ($this->endTime) {
            // Si se especifica hora, usarla
            $timeParts = explode(':', $this->endTime);
            $date->setTime($timeParts[0], $timeParts[1] ?? 0, 59); // Hasta el final del minuto
        } else {
            // Si no se especifica hora, terminar a las 23:59:59
            $date->endOfDay();
        }
        
        return $date;
    }

    public function loadReportData(): void
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat(">", 80) . "\n");
        ReportViewerPageLog::writeRaw("=== LOAD REPORT DATA - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("ESTADO ACTUAL DEL COMPONENTE:\n");
        ReportViewerPageLog::writeRaw("  reportType: " . ($this->reportType ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  startDate: " . ($this->startDate ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  endDate: " . ($this->endDate ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  startTime: " . ($this->startTime ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  endTime: " . ($this->endTime ?? 'null') . "\n");
        
        // Skip if no valid report type
        if (empty($this->reportType)) {
            ReportViewerPageLog::writeRaw("SALIENDO: reportType está vacío\n");
            $this->reportData = collect([]);
            $this->reportStats = [];
            return;
        }

        // Si no hay fechas establecidas, cargar TODOS los datos sin filtros
        if (!$this->startDate || !$this->endDate) {
            ReportViewerPageLog::writeRaw("SIN FECHAS: Cargando datos sin filtros de fecha\n");
            ReportViewerPageLog::writeRaw("  Llamando a getReportDataWithoutFilters() con:\n");
            ReportViewerPageLog::writeRaw("    channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
            ReportViewerPageLog::writeRaw("    invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
            
            $this->reportData = $this->getReportDataWithoutFilters();
            
            ReportViewerPageLog::writeRaw("  Resultados obtenidos: " . $this->reportData->count() . " registros\n");
            
            $this->reportStats = [
                'total_operations' => $this->reportData->count(),
                'total_sales' => $this->reportData->sum('total') ?? 0,
                'total_sales_notes' => $this->getTotalByInvoiceType('sales_note'),
                'total_receipts' => $this->getTotalByInvoiceType('receipt'), 
                'total_invoices' => $this->getTotalByInvoiceType('invoice'),
                'total_cancelled' => $this->getTotalCancelled(),
                'period' => 'Todos los registros históricos',
            ];
            
            ReportViewerPageLog::writeRaw("=== LOAD REPORT DATA - FIN (SIN FECHAS) ===\n");
            return;
        }

        $startDateTime = $this->getStartDateTime();
        $endDateTime = $this->getEndDateTime();
        
        ReportViewerPageLog::writeRaw("CON FECHAS: Procesando con rango de fechas\n");
        ReportViewerPageLog::writeRaw("  startDateTime: " . $startDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  endDateTime: " . $endDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  Llamando a getReportData() con:\n");
        ReportViewerPageLog::writeRaw("    channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("    invoiceType: " . ($this->invoiceType ?? 'null') . "\n");

        // Get data based on report type
        $this->reportData = $this->getReportData($startDateTime, $endDateTime);
        
        ReportViewerPageLog::writeRaw("  Resultados obtenidos: " . $this->reportData->count() . " registros\n");

        // Calculate detailed stats for sales reports
        $periodFormat = 'd/m/Y';
        if ($this->startTime || $this->endTime) {
            $periodFormat = 'd/m/Y H:i';
        }
        
        // Para reportes de productos por canal, necesitamos calcular los resúmenes de manera diferente
        if ($this->reportType === 'products_by_channel') {
            $this->reportStats = $this->calculateProductsByChannelStats($startDateTime, $endDateTime);
        } else {
            $this->reportStats = [
                'total_operations' => $this->reportData->count(),
                'total_sales' => $this->reportData->sum('total') ?? 0,
                'total_sales_notes' => $this->getTotalByInvoiceType('sales_note'),
                'total_receipts' => $this->getTotalByInvoiceType('receipt'), 
                'total_invoices' => $this->getTotalByInvoiceType('invoice'),
                'total_cancelled' => $this->getTotalCancelled(),
                'period' => $startDateTime->format($periodFormat) . ' - ' . $endDateTime->format($periodFormat),
            ];
        }
    }

    protected function calculateProductsByChannelStats($startDateTime, $endDateTime)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("*", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== CALCULATE PRODUCTS BY CHANNEL STATS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("PARÁMETROS RECIBIDOS:\n");
        ReportViewerPageLog::writeRaw("  startDateTime: " . $startDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  endDateTime: " . $endDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  invoiceType: " . ($this->invoiceType ?? 'null') . "\n");

        // Obtener las órdenes completas con los mismos filtros que se usan para la tabla
        $ordersQuery = \App\Models\Order::with(['invoices'])
            ->whereBetween('order_datetime', [$startDateTime, $endDateTime])
            ->where('billed', true);

        // Aplicar filtro por canal de venta si existe
        if ($this->channelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO DE CANAL: " . $this->channelFilter . "\n");
            $ordersQuery->where('service_type', $this->channelFilter);
        }

        // Aplicar filtro por tipo de comprobante si existe
        if ($this->invoiceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO DE TIPO DE COMPROBANTE: " . $this->invoiceType . "\n");
            $this->applyInvoiceTypeFilter($ordersQuery, $this->invoiceType);
        }

        $orders = $ordersQuery->get();
        
        ReportViewerPageLog::writeRaw("ÓRDENES OBTENIDAS: " . $orders->count() . " registros\n");

        // Calcular estadísticas
        $periodFormat = 'd/m/Y';
        if ($this->startTime || $this->endTime) {
            $periodFormat = 'd/m/Y H:i';
        }

        $stats = [
            'total_operations' => $orders->count(),
            'total_sales' => $orders->sum('total') ?? 0,
            'total_sales_notes' => $this->calculateTotalByInvoiceTypeFromOrders($orders, 'sales_note'),
            'total_receipts' => $this->calculateTotalByInvoiceTypeFromOrders($orders, 'receipt'),
            'total_invoices' => $this->calculateTotalByInvoiceTypeFromOrders($orders, 'invoice'),
            'total_cancelled' => $this->calculateTotalCancelledFromOrders($orders),
            'period' => $startDateTime->format($periodFormat) . ' - ' . $endDateTime->format($periodFormat),
        ];

        ReportViewerPageLog::writeRaw("ESTADÍSTICAS CALCULADAS:\n");
        ReportViewerPageLog::writeRaw("  total_operations: " . $stats['total_operations'] . "\n");
        ReportViewerPageLog::writeRaw("  total_sales: " . $stats['total_sales'] . "\n");
        ReportViewerPageLog::writeRaw("  total_sales_notes: " . $stats['total_sales_notes'] . "\n");
        ReportViewerPageLog::writeRaw("  total_receipts: " . $stats['total_receipts'] . "\n");
        ReportViewerPageLog::writeRaw("  total_invoices: " . $stats['total_invoices'] . "\n");
        ReportViewerPageLog::writeRaw("  total_cancelled: " . $stats['total_cancelled'] . "\n");
        ReportViewerPageLog::writeRaw("=== CALCULATE PRODUCTS BY CHANNEL STATS - FIN ===\n");

        return $stats;
    }

    protected function calculateTotalByInvoiceTypeFromOrders($orders, string $type): float
    {
        return $orders
            ->filter(function ($order) use ($type) {
                // Verificar que la orden tenga invoices cargados
                if (!$order->invoices || $order->invoices->isEmpty()) {
                    return false;
                }
                
                // Diferenciación correcta según el tipo solicitado
                switch ($type) {
                    case 'sales_note':
                        // Notas de Venta: Buscar en ambas formas de almacenamiento
                        // 1. Forma actual: invoice_type='receipt' + sunat_status=null
                        $currentForm = $order->invoices->where('invoice_type', 'receipt')
                            ->whereNull('sunat_status')->isNotEmpty();
                        
                        // 2. Forma legacy: invoice_type='sales_note' (cualquier sunat_status)
                        $legacyForm = $order->invoices->where('invoice_type', 'sales_note')->isNotEmpty();
                        
                        return $currentForm || $legacyForm;
                    
                    case 'receipt':
                        // Boletas: invoice_type='receipt' + sunat_status!=null Y no sea 'NO_APLICA' (va a SUNAT)
                        return $order->invoices->where('invoice_type', 'receipt')
                            ->whereNotNull('sunat_status')
                            ->where('sunat_status', '!=', 'NO_APLICA')->isNotEmpty();
                    
                    case 'invoice':
                        // Facturas: invoice_type='invoice'
                        return $order->invoices->where('invoice_type', 'invoice')->isNotEmpty();
                    
                    default:
                        return false;
                }
            })
            ->sum('total');
    }

    protected function calculateTotalCancelledFromOrders($orders): float
    {
        return $orders
            ->filter(function ($order) {
                // Verificar que la orden tenga invoices cargados
                if (!$order->invoices || $order->invoices->isEmpty()) {
                    return false;
                }
                
                // Verificar si la orden tiene facturas anuladas
                return $order->invoices->where('tax_authority_status', 'voided')->isNotEmpty();
            })
            ->sum('total');
    }

    protected function getTotalByInvoiceType(string $type): float
    {
        if (!$this->reportData) {
            return 0;
        }

        return $this->reportData
            ->filter(function ($order) use ($type) {
                // Verificar que la orden tenga invoices cargados
                if (!$order->invoices || $order->invoices->isEmpty()) {
                    return false;
                }
                
                // Diferenciación correcta según el tipo solicitado
                switch ($type) {
                    case 'sales_note':
                        // Notas de Venta: Buscar en ambas formas de almacenamiento
                        // 1. Forma actual: invoice_type='receipt' + sunat_status=null
                        $currentForm = $order->invoices->where('invoice_type', 'receipt')
                            ->whereNull('sunat_status')->isNotEmpty();
                        
                        // 2. Forma legacy: invoice_type='sales_note' (cualquier sunat_status)
                        $legacyForm = $order->invoices->where('invoice_type', 'sales_note')->isNotEmpty();
                        
                        return $currentForm || $legacyForm;
                    
                    case 'receipt':
                        // Boletas: invoice_type='receipt' + sunat_status!=null Y no sea 'NO_APLICA' (va a SUNAT)
                        return $order->invoices->where('invoice_type', 'receipt')
                            ->whereNotNull('sunat_status')
                            ->where('sunat_status', '!=', 'NO_APLICA')->isNotEmpty();
                    
                    case 'invoice':
                        // Facturas: invoice_type='invoice'
                        return $order->invoices->where('invoice_type', 'invoice')->isNotEmpty();
                    
                    default:
                        return false;
                }
            })
            ->sum('total');
    }

    protected function getTotalCancelled(): float
    {
        if (!$this->reportData) {
            return 0;
        }

        return $this->reportData
            ->filter(function ($order) {
                // Verificar que la orden tenga invoices cargados
                if (!$order->invoices || $order->invoices->isEmpty()) {
                    return false;
                }
                
                // Verificar si la orden tiene facturas anuladas
                return $order->invoices->where('tax_authority_status', 'voided')->isNotEmpty();
            })
            ->sum('total');
    }

    protected function getReportDataWithoutFilters()
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("-", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET REPORT DATA WITHOUT FILTERS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("reportType: " . $this->reportType . "\n");
        ReportViewerPageLog::writeRaw("channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $result = match ($this->reportType) {
            // SALES REPORTS - SIN FILTROS DE FECHA
            'all_sales' => $this->getOrdersQueryWithoutFilters(),
            'delivery_sales' => $this->getOrdersQueryWithoutFilters('delivery'),
            'sales_by_waiter' => $this->getSalesByWaiterWithoutFilters(),
            'products_by_channel' => $this->getProductsByChannelWithoutFilters($this->channelFilter, $this->invoiceType),
            
            // PURCHASES REPORTS - SIN FILTROS DE FECHA
            'all_purchases' => $this->getAllPurchasesWithoutFilters(),
            'purchases_by_supplier' => $this->getPurchasesBySupplierWithoutFilters(),
            'purchases_by_category' => $this->getPurchasesByCategoryWithoutFilters(),
            
            // FINANCE REPORTS - SIN FILTROS DE FECHA
            'accounting_reports' => $this->getAccountingReportsWithoutFilters(),
            
            // Otros reportes también sin filtros...
            default => collect([])
        };
        
        ReportViewerPageLog::writeRaw("Resultado obtenido: " . $result->count() . " registros\n");
        ReportViewerPageLog::writeRaw("=== GET REPORT DATA WITHOUT FILTERS - FIN ===\n");
        
        return $result;
    }

    protected function getReportData($startDateTime, $endDateTime)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("+", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET REPORT DATA - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("PARÁMETROS:\n");
        ReportViewerPageLog::writeRaw("  startDateTime: " . $startDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  endDateTime: " . $endDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  reportType: " . $this->reportType . "\n");
        ReportViewerPageLog::writeRaw("  channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $result = match ($this->reportType) {
            // SALES REPORTS
            'all_sales' => $this->getOrdersQuery($startDateTime, $endDateTime),
            'delivery_sales' => $this->getOrdersQuery($startDateTime, $endDateTime, 'delivery'),
            'sales_by_waiter' => $this->getSalesByWaiter($startDateTime, $endDateTime),
            'products_by_channel' => $this->getProductsByChannel($startDateTime, $endDateTime, $this->channelFilter, $this->invoiceType),
            'payment_methods' => $this->getPaymentMethods($startDateTime, $endDateTime),
            
            // PURCHASES REPORTS
            'all_purchases' => $this->getAllPurchases($startDateTime, $endDateTime),
            'purchases_by_supplier' => $this->getPurchasesBySupplier($startDateTime, $endDateTime),
            'purchases_by_category' => $this->getPurchasesByCategory($startDateTime, $endDateTime),
            
            // FINANCE REPORTS
            'cash_register' => $this->getCashRegisterMovements($startDateTime, $endDateTime),
            'profits' => $this->getProfits($startDateTime, $endDateTime),
            'daily_closing' => $this->getDailyClosing($startDateTime, $endDateTime),
            'accounting_reports' => $this->getAccountingReports($startDateTime, $endDateTime),
            
            // OPERATIONS REPORTS
            'sales_by_user' => $this->getSalesByUser($startDateTime, $endDateTime),
            'user_activity' => $this->getUserActivity($startDateTime, $endDateTime),
            'system_logs' => $this->getSystemLogs($startDateTime, $endDateTime),
            
            default => collect([]),
        };
        
        ReportViewerPageLog::writeRaw("RESULTADO: " . $result->count() . " registros\n");
        ReportViewerPageLog::writeRaw("=== GET REPORT DATA - FIN ===\n");
        
        return $result;
    }
    
    protected function getOrdersQuery($startDateTime, $endDateTime, $serviceType = null)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("=", 50) . "\n");
        ReportViewerPageLog::writeRaw("=== GET ORDERS QUERY - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("startDateTime: $startDateTime\n");
        ReportViewerPageLog::writeRaw("endDateTime: $endDateTime\n");
        ReportViewerPageLog::writeRaw("serviceType: " . ($serviceType ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("this->channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("this->invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $query = Order::whereBetween('order_datetime', [$startDateTime, $endDateTime])
            ->where('billed', true)
            ->with([
                'customer',           // Cliente formal de la order
                'user', 
                'table',              // Para mostrar mesa cuando no hay cliente
                'cashRegister', 
                'invoices.customer'   // Cliente formal de la invoice + campo client_name para comandas rápidas
            ]);
            
        ReportViewerPageLog::writeRaw("Query base construida con whereBetween order_datetime y where('billed', true)\n");
        
        if ($serviceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO serviceType: $serviceType\n");
            $query->where('service_type', $serviceType);
        }
        
        // Aplicar filtro por canal de venta si está presente
        if ($this->channelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO channelFilter: $this->channelFilter\n");
            \Log::info('getOrdersQuery - Aplicando filtro de canal: ' . $this->channelFilter);
            $query->where('service_type', $this->channelFilter);
        } else {
            ReportViewerPageLog::writeRaw("NO SE APLICA FILTRO DE CANAL\n");
        }
        
        // Aplicar filtro por tipo de comprobante si está presente
        ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        $this->applyInvoiceTypeFilter($query, $this->invoiceType);
        
        // Log del SQL completo
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        ReportViewerPageLog::writeRaw("SQL FINAL: $sql\n");
        ReportViewerPageLog::writeRaw("BINDINGS: " . json_encode($bindings) . "\n");
        
        // Log the SQL query for debugging
        \Log::info('getOrdersQuery - Aplicando filtros finales');
        \Log::info('getOrdersQuery - SQL: ' . $sql);
        \Log::info('getOrdersQuery - Bindings: ' . json_encode($bindings));
        
        $results = $query->orderBy('order_datetime', 'desc')->get();
        
        ReportViewerPageLog::writeRaw("RESULTADOS: " . $results->count() . " registros\n");
        
        if ($results->count() > 0) {
            // Muestra los tipos de servicio encontrados
            $serviceTypes = $results->pluck('service_type')->unique()->values();
            ReportViewerPageLog::writeRaw("TIPOS DE SERVICIO ENCONTRADOS: " . json_encode($serviceTypes->toArray()) . "\n");
        }
        
        // Debug invoice types
        $invoiceTypes = $results->flatMap(function($order) {
            return $order->invoices->pluck('invoice_type');
        })->unique()->values();
        \Log::info('Invoice types found in results: ' . json_encode($invoiceTypes->toArray()));
        
        ReportViewerPageLog::writeRaw("=== GET ORDERS QUERY - FIN ===\n");
        
        return $results;
    }

    protected function getOrdersQueryWithoutFilters($serviceType = null)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("~", 50) . "\n");
        ReportViewerPageLog::writeRaw("=== GET ORDERS QUERY WITHOUT FILTERS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("serviceType: " . ($serviceType ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("this->channelFilter: " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("this->invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $query = Order::where('billed', true)
            ->with([
                'customer',           // Cliente formal de la order
                'user', 
                'table',              // Para mostrar mesa cuando no hay cliente
                'cashRegister', 
                'invoices.customer'   // Cliente formal de la invoice + campo client_name para comandas rápidas
            ]);
            
        ReportViewerPageLog::writeRaw("Query base construida con where('billed', true)\n");
            
        if ($serviceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO serviceType: $serviceType\n");
            $query->where('service_type', $serviceType);
        }
        
        // Aplicar filtro por canal de venta si está presente
        if ($this->channelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO channelFilter: $this->channelFilter\n");
            \Log::info('getOrdersQueryWithoutFilters - Aplicando filtro de canal: ' . $this->channelFilter);
            $query->where('service_type', $this->channelFilter);
        } else {
            ReportViewerPageLog::writeRaw("NO SE APLICA FILTRO DE CANAL\n");
        }
        
        // Aplicar filtro por tipo de comprobante si está presente
        ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        $this->applyInvoiceTypeFilter($query, $this->invoiceType);
        
        // Log del SQL completo
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        ReportViewerPageLog::writeRaw("SQL FINAL: $sql\n");
        ReportViewerPageLog::writeRaw("BINDINGS: " . json_encode($bindings) . "\n");
        
        // Log the SQL query for debugging
        \Log::info('getOrdersQueryWithoutFilters - Aplicando filtros finales');
        \Log::info('getOrdersQueryWithoutFilters - SQL: ' . $sql);
        \Log::info('getOrdersQueryWithoutFilters - Bindings: ' . json_encode($bindings));
        
        $results = $query->orderBy('order_datetime', 'desc')->get();
        
        ReportViewerPageLog::writeRaw("RESULTADOS: " . $results->count() . " registros\n");
        
        if ($results->count() > 0) {
            // Muestra los tipos de servicio encontrados
            $serviceTypes = $results->pluck('service_type')->unique()->values();
            ReportViewerPageLog::writeRaw("TIPOS DE SERVICIO ENCONTRADOS: " . json_encode($serviceTypes->toArray()) . "\n");
        }
        
        ReportViewerPageLog::writeRaw("=== GET ORDERS QUERY WITHOUT FILTERS - FIN ===\n");
        
        return $results;
    }

    protected function getSalesByWaiterWithoutFilters()
    {
        return Order::where('billed', true)
            ->with(['user'])
            ->selectRaw('user_id, users.name as waiter_name, COUNT(*) as total_orders, SUM(total) as total_sales')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->groupBy('user_id', 'users.name')
            ->orderBy('total_sales', 'desc')
            ->get();
    }
    
    protected function getProductsByChannelWithoutFilters($channelFilter = null, $invoiceType = null)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("*", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET PRODUCTS BY CHANNEL WITHOUT FILTERS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("PARÁMETROS RECIBIDOS:\n");
        ReportViewerPageLog::writeRaw("  channelFilter (parámetro): " . ($channelFilter ?? 'null') . " (tipo: " . gettype($channelFilter) . ")\n");
        ReportViewerPageLog::writeRaw("  invoiceType (parámetro): " . ($invoiceType ?? 'null') . " (tipo: " . gettype($invoiceType) . ")\n");
        ReportViewerPageLog::writeRaw("  this->channelFilter (propiedad): " . ($this->channelFilter ?? 'null') . " (tipo: " . gettype($this->channelFilter) . ")\n");
        ReportViewerPageLog::writeRaw("  this->invoiceType (propiedad): " . ($this->invoiceType ?? 'null') . " (tipo: " . gettype($this->invoiceType) . ")\n");
        
        \Log::info('getProductsByChannelWithoutFilters - channelFilter: ' . ($channelFilter ?? 'null'));
        \Log::info('getProductsByChannelWithoutFilters - invoiceType: ' . ($invoiceType ?? 'null'));
        
        ReportViewerPageLog::writeRaw("CONSTRUYENDO QUERY INICIAL...\n");
        
        $query = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.billed', true);
            
        ReportViewerPageLog::writeRaw("Query base construida con joins y where('orders.billed', true)\n");
        
        // Aplicar filtro por canal de venta PRIMERO (antes que tipo de comprobante)
        if ($channelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO channelFilter desde PARÁMETRO: $channelFilter\n");
            \Log::info('Aplicando filtro de canal (param): ' . $channelFilter);
            $query->where('orders.service_type', $channelFilter);
        } elseif ($this->channelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO channelFilter desde PROPIEDAD: $this->channelFilter\n");
            \Log::info('Aplicando filtro de canal (instancia): ' . $this->channelFilter);
            $query->where('orders.service_type', $this->channelFilter);
        } else {
            ReportViewerPageLog::writeRaw("NO SE APLICA FILTRO DE CANAL\n");
        }
        
        // Aplicar filtro por tipo de comprobante DESPUÉS
        if ($invoiceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType desde parámetro: $invoiceType\n");
            $this->applyInvoiceTypeFilter($query, $invoiceType);
        } else {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType desde propiedad: " . ($this->invoiceType ?? 'null') . "\n");
            $this->applyInvoiceTypeFilter($query);
        }
        
        ReportViewerPageLog::writeRaw("EJECUTANDO QUERY FINAL...\n");
        
        // Log del SQL completo antes de ejecutar
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        ReportViewerPageLog::writeRaw("SQL FINAL: $sql\n");
        ReportViewerPageLog::writeRaw("BINDINGS: " . json_encode($bindings) . "\n");
        \Log::info('getProductsByChannelWithoutFilters - SQL: ' . $sql);
        \Log::info('getProductsByChannelWithoutFilters - Bindings: ' . json_encode($bindings));
            
        $results = $query->select(
                'orders.service_type',
                \DB::raw('SUM(order_details.quantity) as total_quantity'),
                \DB::raw('SUM(order_details.subtotal) as total_sales')
            )
            ->groupBy('orders.service_type')
            ->orderBy('orders.service_type')
            ->get();
            
        ReportViewerPageLog::writeRaw("RESULTADOS OBTENIDOS: " . $results->count() . " registros\n");
        
        // Log de muestra de los primeros 3 resultados
        if ($results->count() > 0) {
            ReportViewerPageLog::writeRaw("MUESTRA DE PRIMEROS 3 RESULTADOS (agrupado por canal):\n");
            foreach ($results->take(3) as $index => $result) {
                ReportViewerPageLog::writeRaw("  [$index] service_type: " . ($result->service_type ?? 'null') . ", total_quantity: " . ($result->total_quantity ?? 'null') . ", total_sales: " . ($result->total_sales ?? 'null') . "\n");
            }
        }
        
        ReportViewerPageLog::writeRaw("=== GET PRODUCTS BY CHANNEL WITHOUT FILTERS - FIN ===\n");
        
        return $results;
    }
    
    protected function applyInvoiceTypeFilter($query, $invoiceType = null)
    {
        $currentInvoiceType = $invoiceType ?? $this->invoiceType;
        
        if ($currentInvoiceType) {
            \Log::info("Aplicando filtro de tipo de comprobante: {$currentInvoiceType}");
            
            // Filtrar EXCLUSIVAMENTE por serie (más confiable que invoice_type)
            $query->whereHas('invoices', function ($invoiceQuery) use ($currentInvoiceType) {
                switch ($currentInvoiceType) {
                case 'sales_note':
                    $invoiceQuery->where('series', 'LIKE', 'NV%');
                    break;
                case 'receipt':
                    $invoiceQuery->where('series', 'LIKE', 'B%');
                    break;
                case 'invoice':
                    $invoiceQuery->where('series', 'LIKE', 'F%');
                    break;
                default:
                    // Si no es un tipo reconocido, usar invoice_type como fallback
                    $invoiceQuery->where('invoice_type', $currentInvoiceType);
                    break;
            }
            });
        } else {
            \Log::info("No se aplica filtro de tipo de comprobante - invoiceType es: " . ($currentInvoiceType ?? 'null'));
        }
    }
    
    protected function getProductsByChannel($startDateTime, $endDateTime, $channelFilter = null, $invoiceType = null)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("#", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET PRODUCTS BY CHANNEL - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("PARÁMETROS RECIBIDOS:\n");
        ReportViewerPageLog::writeRaw("  startDateTime: " . $startDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  endDateTime: " . $endDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  channelFilter (parámetro): " . ($channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  invoiceType (parámetro): " . ($invoiceType ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  this->channelFilter (propiedad): " . ($this->channelFilter ?? 'null') . "\n");
        ReportViewerPageLog::writeRaw("  this->invoiceType (propiedad): " . ($this->invoiceType ?? 'null') . "\n");
        
        // Log de depuración para verificar el filtro de canal
        $currentChannelFilter = $channelFilter ?? $this->channelFilter;
        $currentInvoiceType = $invoiceType ?? $this->invoiceType;
        \Log::info('getProductsByChannel - channelFilter: ' . ($currentChannelFilter ?? 'null'));
        \Log::info('getProductsByChannel - startDateTime: ' . $startDateTime->format('Y-m-d H:i:s'));
        \Log::info('getProductsByChannel - endDateTime: ' . $endDateTime->format('Y-m-d H:i:s'));
        
        ReportViewerPageLog::writeRaw("CONSTRUYENDO QUERY INICIAL...\n");
        
        $query = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
            ->where('orders.billed', true);
            
        ReportViewerPageLog::writeRaw("Query base construida con whereBetween order_datetime y where('orders.billed', true)\n");
        
        // Aplicar filtro por canal de venta PRIMERO (antes que tipo de comprobante)
        if ($currentChannelFilter) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO DE CANAL PRIMERO: $currentChannelFilter\n");
            \Log::info('Aplicando filtro de canal: ' . $currentChannelFilter);
            $query->where('orders.service_type', $currentChannelFilter);
        } else {
            ReportViewerPageLog::writeRaw("NO SE APLICA FILTRO DE CANAL\n");
        }
        
        // Aplicar filtro por tipo de comprobante DESPUÉS
        if ($currentInvoiceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO DE TIPO DE COMPROBANTE: $currentInvoiceType\n");
        } else {
            ReportViewerPageLog::writeRaw("NO SE APLICA FILTRO DE TIPO DE COMPROBANTE\n");
        }
        $this->applyInvoiceTypeFilter($query, $currentInvoiceType);
        
        // Log del SQL completo antes de ejecutar
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        ReportViewerPageLog::writeRaw("SQL FINAL: $sql\n");
        ReportViewerPageLog::writeRaw("BINDINGS: " . json_encode($bindings) . "\n");
        \Log::info('getProductsByChannel - SQL: ' . $sql);
        \Log::info('getProductsByChannel - Bindings: ' . json_encode($bindings));
            
        $results = $query->select(
                'orders.service_type',
                \DB::raw('SUM(order_details.quantity) as total_quantity'),
                \DB::raw('SUM(order_details.subtotal) as total_sales')
            )
            ->groupBy('orders.service_type')
            ->orderBy('orders.service_type')
            ->get();
            
        ReportViewerPageLog::writeRaw("RESULTADOS OBTENIDOS: " . $results->count() . " registros\n");
        
        // Log de muestra de los primeros 3 resultados
        if ($results->count() > 0) {
            ReportViewerPageLog::writeRaw("MUESTRA DE PRIMEROS 3 RESULTADOS (agrupado por canal):\n");
            foreach ($results->take(3) as $index => $result) {
                ReportViewerPageLog::writeRaw("  [$index] service_type: " . ($result->service_type ?? 'null') . ", total_quantity: " . ($result->total_quantity ?? 'null') . ", total_sales: " . ($result->total_sales ?? 'null') . "\n");
            }
        }
        
        ReportViewerPageLog::writeRaw("=== GET PRODUCTS BY CHANNEL - FIN ===\n");
            
        return $results;
    }
    
    protected function getSalesByWaiter($startDateTime, $endDateTime)
    {
        return \App\Models\Order::join('users', 'orders.employee_id', '=', 'users.id')
            ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
            ->where('orders.billed', true)
            ->select(
                'users.name',
                \DB::raw('COUNT(orders.id) as total_orders'),
                \DB::raw('SUM(orders.total) as total_sales')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();
    }
    
    protected function getPaymentMethods($startDateTime, $endDateTime)
    {
        return \App\Models\Order::whereBetween('order_datetime', [$startDateTime, $endDateTime])
            ->where('billed', true)
            ->select(
                'payment_method',
                \DB::raw('COUNT(id) as total_orders'),
                \DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('payment_method')
            ->orderBy('payment_method')
            ->get();
    }
    
    // PURCHASES REPORTS
    protected function getAllPurchases($startDateTime, $endDateTime)
    {
        return \App\Models\Purchase::whereBetween('purchase_date', [$startDateTime, $endDateTime])
            ->with(['supplier', 'creator'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }
    
    protected function getPurchasesBySupplier($startDateTime, $endDateTime)
    {
        return \App\Models\Purchase::join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchases.purchase_date', [$startDateTime, $endDateTime])
            ->select(
                'suppliers.business_name as supplier_name',
                \DB::raw('COUNT(purchases.id) as total_purchases'),
                \DB::raw('SUM(purchases.total) as total_amount')
            )
            ->groupBy('suppliers.id', 'suppliers.business_name')
            ->orderBy('suppliers.business_name')
            ->get();
    }
    
    protected function getPurchasesByCategory($startDateTime, $endDateTime)
    {
        return \App\Models\PurchaseDetail::join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->whereBetween('purchases.purchase_date', [$startDateTime, $endDateTime])
            ->select(
                'product_categories.name as category_name',
                \DB::raw('SUM(purchase_details.quantity) as total_quantity'),
                \DB::raw('SUM(purchase_details.subtotal) as total_amount')
            )
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('product_categories.name')
            ->get();
    }
    
    // FINANCE REPORTS
    protected function getCashRegisterMovements($startDateTime, $endDateTime)
    {
        return \App\Models\CashRegister::whereBetween('opening_datetime', [$startDateTime, $endDateTime])
            ->with(['openedBy'])
            ->orderBy('opening_datetime', 'desc')
            ->get();
    }
    
    protected function getProfits($startDateTime, $endDateTime)
    {
        return \App\Models\Order::whereBetween('order_datetime', [$startDateTime, $endDateTime])
            ->where('billed', true)
            ->select(
                \DB::raw('DATE(order_datetime) as date'),
                \DB::raw('SUM(total) as total_sales'),
                \DB::raw('COUNT(id) as total_orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
    
    protected function getDailyClosing($startDateTime, $endDateTime)
    {
        return \App\Models\CashRegister::whereBetween('closing_datetime', [$startDateTime, $endDateTime])
            ->whereNotNull('closing_datetime')
            ->with(['closedBy'])
            ->orderBy('closing_datetime', 'desc')
            ->get();
    }
    
    // OPERATIONS REPORTS
    protected function getSalesByUser($startDateTime, $endDateTime)
    {
        return \App\Models\Order::join('users', 'orders.employee_id', '=', 'users.id')
            ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
            ->where('orders.billed', true)
            ->select(
                'users.name',
                \DB::raw('COUNT(orders.id) as total_orders'),
                \DB::raw('SUM(orders.total) as total_sales')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();
    }
    
    protected function getUserActivity($startDateTime, $endDateTime)
    {
        return \App\Models\User::select(
                'users.name',
                'users.email',
                'users.last_login_at',
                \DB::raw('COUNT(orders.id) as orders_created')
            )
            ->leftJoin('orders', function($join) use ($startDateTime, $endDateTime) {
                $join->on('users.id', '=', 'orders.employee_id')
                     ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime]);
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.last_login_at')
            ->orderBy('users.name')
            ->get();
    }
    
    protected function getSystemLogs($startDateTime, $endDateTime)
    {
        // KISS: Retornamos logs básicos del sistema
        return collect([
            (object)[
                'date' => $startDateTime->format('Y-m-d'),
                'event' => 'Sistema iniciado',
                'user' => 'Sistema',
                'description' => 'Inicio de operaciones del día'
            ],
            (object)[
                'date' => $endDateTime->format('Y-m-d'),
                'event' => 'Consulta de reportes',
                'user' => auth()->user()->name ?? 'Usuario',
                'description' => 'Generación de reporte de logs del sistema'
            ]
        ]);
    }

    public function viewOrderDetail($orderId): void
    {
        $this->redirect(route('filament.admin.resources.orders.view', ['record' => $orderId]));
    }

    public function printOrder($orderId): void
    {
        $order = Order::find($orderId);
        if ($order && $order->invoice) {
            $printUrl = route('print.invoice', ['invoice' => $order->invoice->id]);
            $this->dispatch('open-print-window', url: $printUrl);
        } else {
            Notification::make()
                ->title('❌ No se puede imprimir')
                ->body('La orden no tiene comprobante asociado')
                ->danger()
                ->send();
        }
    }

    public function exportReport()
    {
        // Inicializar el logger de exportación de Excel
        $logger = new ExcelExportLogger();
        
        // Escribir en el log individual
        file_put_contents(storage_path('logs/descargaexcel.log'), "\n=== INICIO EXPORTACIÓN EXCEL ===\n", FILE_APPEND);
        file_put_contents(storage_path('logs/descargaexcel.log'), "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        file_put_contents(storage_path('logs/descargaexcel.log'), "Tipo de reporte: " . $this->reportType . "\n", FILE_APPEND);
        file_put_contents(storage_path('logs/descargaexcel.log'), "Categoría: " . $this->category . "\n", FILE_APPEND);
        
        // Log inicial con el logger nuevo
        $logger->logStep('export_started', [
            'report_type' => $this->reportType,
            'category' => $this->category,
            'date_range' => $this->dateRange,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate
        ]);
        
        try {
            // Obtener los datos según el tipo de reporte
            file_put_contents(storage_path('logs/descargaexcel.log'), "Obteniendo datos para exportación...\n", FILE_APPEND);
            $logger->logStep('getting_report_data');
            $data = $this->getReportDataForExport();
            $logger->logStep('report_data_obtained', [
                'record_count' => count($data),
                'data_sample' => array_slice($data, 0, 3) // Primeros 3 registros como muestra
            ]);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Datos obtenidos: " . count($data) . " registros\n", FILE_APPEND);
            
            if (empty($data)) {
                file_put_contents(storage_path('logs/descargaexcel.log'), "SIN DATOS PARA EXPORTAR\n", FILE_APPEND);
                Notification::make()
                    ->title('Sin datos')
                    ->body('No hay datos para exportar')
                    ->warning()
                    ->send();
                return null;
            }
            
            file_put_contents(storage_path('logs/descargaexcel.log'), "Creando archivo Excel...\n", FILE_APPEND);
            $logger->logStep('creating_spreadsheet');
            // Crear el archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $logger->logStep('spreadsheet_created', [
                'active_sheet_name' => $sheet->getTitle()
            ]);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Spreadsheet creado exitosamente\n", FILE_APPEND);
            
            // Configurar headers según el tipo de reporte
            file_put_contents(storage_path('logs/descargaexcel.log'), "Configurando headers...\n", FILE_APPEND);
            $headers = $this->getHeadersForReport();
            file_put_contents(storage_path('logs/descargaexcel.log'), "Headers: " . implode(', ', $headers) . "\n", FILE_APPEND);
            
            $column = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($column . '1', $header);
                $column++;
            }
            
            // Aplicar estilos al header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            ];
            $lastColumn = chr(ord('A') + count($headers) - 1);
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Headers configurados y estilizados\n", FILE_APPEND);
            
            // Data
            file_put_contents(storage_path('logs/descargaexcel.log'), "📊 Insertando datos en el Excel...\n", FILE_APPEND);
            $row = 2;
            $rowCount = 0;
            foreach ($data as $item) {
                $column = 'A';
                $rowData = $this->formatRowData($item);
                $columnIndex = 0;
                foreach ($rowData as $value) {
                    // Handle different data types properly
                    if (is_numeric($value) && strpos($value, '/') === false && strpos($value, ':') === false) {
                        // It's a number, set as numeric value
                        $sheet->setCellValueExplicit($column . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } elseif (strpos($value, '/') !== false && strlen($value) <= 16) {
                        // It's likely a date in d/m/Y format, convert to Excel date
                        $dateParts = explode('/', $value);
                        if (count($dateParts) == 3) {
                            $excelDate = Date::PHPToExcel(mktime(0, 0, 0, (int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2]));
                            $sheet->setCellValue($column . $row, $excelDate);
                            $sheet->getStyle($column . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                        } else {
                            $sheet->setCellValue($column . $row, $value);
                        }
                    } elseif (strpos($value, ':') !== false && strlen($value) <= 16) {
                        // It's likely a time, convert to Excel time
                        $timeParts = explode(':', $value);
                        if (count($timeParts) >= 2) {
                            $excelTime = Date::PHPToExcel(mktime((int)$timeParts[0], (int)$timeParts[1], 0, 1, 1, 1970));
                            $sheet->setCellValue($column . $row, $excelTime);
                            $sheet->getStyle($column . $row)->getNumberFormat()->setFormatCode('hh:mm');
                        } else {
                            $sheet->setCellValue($column . $row, $value);
                        }
                    } else {
                        $sheet->setCellValue($column . $row, $value);
                    }
                    $column++;
                    $columnIndex++;
                }
                $row++;
                $rowCount++;
                
                if ($rowCount % 100 == 0) {
                    file_put_contents(storage_path('logs/descargaexcel.log'), "Procesadas " . $rowCount . " filas...\n", FILE_APPEND);
                }
            }
            file_put_contents(storage_path('logs/descargaexcel.log'), "Datos insertados: " . $rowCount . " filas\n", FILE_APPEND);
            
            // Auto-size columns
            file_put_contents(storage_path('logs/descargaexcel.log'), "Ajustando anchos de columnas...\n", FILE_APPEND);
            foreach (range('A', $lastColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            file_put_contents(storage_path('logs/descargaexcel.log'), "Columnas ajustadas\n", FILE_APPEND);
            
            // Create filename
            file_put_contents(storage_path('logs/descargaexcel.log'), "Generando nombre de archivo...\n", FILE_APPEND);
            $filename = $this->getFilenameForReport();
            file_put_contents(storage_path('logs/descargaexcel.log'), "Nombre de archivo: " . $filename . "\n", FILE_APPEND);
            
            // Save and download
            file_put_contents(storage_path('logs/descargaexcel.log'), "Guardando archivo temporal...\n", FILE_APPEND);
            $logger->logStep('saving_excel_file');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Crear directorio temporal si no existe
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Generar nombre único para el archivo
            $tempFile = $tempDir . '/reporte_' . uniqid() . '.xlsx';
            file_put_contents(storage_path('logs/descargaexcel.log'), "Archivo temporal: " . $tempFile . "\n", FILE_APPEND);
            
            $writer->save($tempFile);
            $logger->logStep('excel_file_saved', [
                'file_path' => $tempFile,
                'file_size' => filesize($tempFile)
            ]);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Archivo Excel guardado exitosamente\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Tamaño del archivo: " . filesize($tempFile) . " bytes\n", FILE_APPEND);
            
            file_put_contents(storage_path('logs/descargaexcel.log'), "EXPORTACIÓN COMPLETADA EXITOSAMENTE\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "=== FIN EXPORTACIÓN EXCEL ===\n", FILE_APPEND);
            
            // Validar el archivo antes de enviarlo
            $logger->logStep('validating_excel_file');
            $logger->logFileInfo($tempFile);
            $validationResults = $logger->validateExcelFile($tempFile);
            
            if ($validationResults['overall_status'] !== 'passed') {
                $logger->logError('Excel file validation failed', $validationResults);
                throw new \Exception("El archivo Excel generado no es válido: " . $validationResults['summary']);
            }
            
            $logger->logStep('validation_passed', $validationResults);
            
            // Retornar la respuesta de descarga
            $logger->logStep('sending_download_response', [
                'filename' => $filename,
                'file_size' => filesize($tempFile)
            ]);
            
            return response()->download($tempFile, $filename)->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/descargaexcel.log'), "ERROR EN EXPORTACIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Archivo: " . $e->getFile() . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Línea: " . $e->getLine() . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            file_put_contents(storage_path('logs/descargaexcel.log'), "=== FIN EXPORTACIÓN CON ERROR ===\n", FILE_APPEND);
            
            // Log del error con el nuevo logger
            $logger->logError('Export failed with exception', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'report_type' => $this->reportType,
                'category' => $this->category
            ]);
            
            // Guardar el log detallado antes de lanzar la excepción
            $logFile = $logger->saveDetailedLog();
            
            // Agregar información del log al mensaje de error
            $enhancedMessage = $e->getMessage() . " (Detalles guardados en: {$logFile})";
            throw new \Exception($enhancedMessage, 0, $e);
        }
    }
    
    private function getReportDataForExport()
    {
        switch ($this->reportType) {
            case 'products_by_channel':
                $data = $this->reportData->map(function ($item) {
                    return [
                        'product_name' => $item->product_name,
                        'channel_label' => $this->getChannelLabel($item->service_type),
                        'quantity' => $item->total_quantity,
                        'total_sales' => $item->total_sales,
                    ];
                });
                
                // Agregar fila de totales
                $data->push([
                    'product_name' => 'TOTAL GENERAL',
                    'channel_label' => '',
                    'quantity' => $this->reportData->sum('total_quantity'),
                    'total_sales' => $this->reportData->sum('total_sales'),
                ]);
                
                return $data;
            case 'accounting_reports':
                // Para accounting_reports, recargar los datos para asegurar que se apliquen los mismos filtros
                if ($this->startDate && $this->endDate) {
                    $startDateTime = $this->getStartDateTime();
                    $endDateTime = $this->getEndDateTime();
                    $data = $this->getAccountingReports($startDateTime, $endDateTime);
                } else {
                    $data = $this->getAccountingReportsWithoutFilters();
                }
                
                // DEBUG: Log para verificar qué datos están llegando al Excel
                ReportViewerPageLog::writeRaw("\n=== DEBUG EXCEL ACCOUNTING REPORTS ===\n");
                ReportViewerPageLog::writeRaw("Total de registros para Excel: " . $data->count() . "\n");
                
                // Mostrar primeros 5 registros para debugging
                $data->take(5)->each(function ($item, $index) {
                    ReportViewerPageLog::writeRaw("Registro [$index]:\n");
                    ReportViewerPageLog::writeRaw("  ID: " . $item->id . "\n");
                    ReportViewerPageLog::writeRaw("  Serie: " . $item->series . "\n");
                    ReportViewerPageLog::writeRaw("  Número: " . $item->number . "\n");
                    ReportViewerPageLog::writeRaw("  invoice_type: " . $item->invoice_type . "\n");
                    ReportViewerPageLog::writeRaw("  sunat_status: " . ($item->sunat_status ?? 'null') . "\n");
                    ReportViewerPageLog::writeRaw("  Tipo formateado: " . $this->getInvoiceTypeLabelForAccounting($item) . "\n");
                    ReportViewerPageLog::writeRaw("  ---\n");
                });
                
                // Contar tipos de comprobantes
                $tiposContados = [];
                $data->each(function ($item) use (&$tiposContados) {
                    $tipoFormateado = $this->getInvoiceTypeLabelForAccounting($item);
                    $tiposContados[$tipoFormateado] = ($tiposContados[$tipoFormateado] ?? 0) + 1;
                });
                
                ReportViewerPageLog::writeRaw("RESUMEN DE TIPOS EN EXCEL:\n");
                foreach ($tiposContados as $tipo => $cantidad) {
                    ReportViewerPageLog::writeRaw("  $tipo: $cantidad\n");
                }
                ReportViewerPageLog::writeRaw("=== FIN DEBUG EXCEL ===\n");
                
                return $data->map(function ($item) {
                    return [
                        'issue_date' => $item->issue_date->format('d/m/Y'),
                        'invoice_type' => $this->getInvoiceTypeLabelForAccounting($item),
                        'series_number' => $item->series . '-' . $item->number,
                        'customer' => $item->customer ? $item->customer->name : ($item->client_name ?? 'Cliente no registrado'),
                        'document_number' => $this->getCustomerDocumentNumberForAccounting($item),
                        'total' => $item->total,
                        'status' => $this->getInvoiceStatusLabel($item),
                    ];
                });
            case 'all_sales':
            case 'delivery_sales':
                return $this->reportData->map(function ($item) {
                    return [
                        'created_at' => $item->order_datetime->format('d/m/Y H:i'),
                        'cash_register' => $item->cashRegister->name ?? 'N/A',
                        'customer' => $item->customer->name ?? ($item->client_name ?? 'Cliente no registrado'),
                        'invoice_type' => $this->getInvoiceTypeLabel($item),
                        'channel_label' => $this->getChannelLabel($item->service_type),
                        'payment_method' => $this->getPaymentMethodLabel($item->payment_method),
                        'total' => $item->total,
                        'status' => $item->status,
                    ];
                });
            case 'sales_by_waiter':
            case 'sales_by_user':
                return $this->reportData->map(function ($item) {
                    return [
                        'user_name' => $item->name,
                        'orders_count' => $item->total_orders,
                        'total_sales' => $item->total_sales,
                    ];
                });
            case 'payment_methods':
                return $this->reportData->map(function ($item) {
                    return [
                        'payment_method' => $this->getPaymentMethodLabel($item->payment_method),
                        'operations_count' => $item->total_orders,
                        'total_sales' => $item->total_sales,
                    ];
                });
            case 'all_purchases':
                return $this->reportData->map(function ($item) {
                    return [
                        'created_at' => $item->purchase_date->format('d/m/Y'),
                        'supplier' => $item->supplier->business_name,
                        'user' => $item->creator->name,
                        'total' => $item->total,
                    ];
                });
            case 'purchases_by_supplier':
                return $this->reportData->map(function ($item) {
                    return [
                        'supplier' => $item->supplier_name,
                        'purchases_count' => $item->total_purchases,
                        'total_purchases' => $item->total_amount,
                    ];
                });
            case 'purchases_by_category':
                return $this->reportData->map(function ($item) {
                    return [
                        'category' => $item->category_name,
                        'quantity' => $item->total_quantity,
                        'total' => $item->total_amount,
                    ];
                });
            case 'cash_register':
                return $this->reportData->map(function ($item) {
                    return [
                        'date' => $item->opening_datetime->format('d/m/Y H:i'),
                        'cash_register' => $item->name,
                        'opening_user' => $item->openedBy->name,
                        'closing_user' => $item->closedBy->name ?? 'Abierta',
                        'initial_balance' => $item->initial_balance,
                        'sales' => $item->total_sales,
                        'final_balance' => $item->final_balance,
                        'status' => $item->status,
                    ];
                });
            case 'profits':
                return $this->reportData->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'product' => 'Resumen',
                        'quantity_sold' => $item->total_orders,
                        'total_cost' => 'N/A',
                        'total_sale' => $item->total_sales,
                        'profit' => 'N/A',
                    ];
                });
            case 'daily_closing':
                return $this->reportData->map(function ($item) {
                    return [
                        'date' => $item->closing_datetime->format('d/m/Y H:i'),
                        'cash_register' => $item->name,
                        'total_sales' => $item->total_sales,
                        'total_purchases' => $item->total_purchases,
                        'final_balance' => $item->final_balance,
                    ];
                });
            case 'user_activity':
                return $this->reportData->map(function ($item) {
                    return [
                        'user' => $item->name,
                        'action' => 'Actividad',
                        'created_at' => $item->last_login_at ? $item->last_login_at->format('d/m/Y H:i') : 'Nunca',
                        'ip_address' => 'N/A',
                    ];
                });
            case 'system_logs':
                return $this->reportData->map(function ($item) {
                    return [
                        'user' => $item->user,
                        'action' => $item->event,
                        'description' => $item->description,
                        'created_at' => $item->date,
                        'ip_address' => 'N/A',
                    ];
                });
            default:
                return [];
        }
    }
    
    private function getHeadersForReport()
    {
        switch ($this->reportType) {
            case 'products_by_channel':
                return ['Producto', 'Canal de Venta', 'Cantidad', 'Total Ventas'];
            case 'accounting_reports':
                return ['Fecha Emisión', 'Tipo Comprobante', 'Número', 'Cliente', 'N° Documento', 'Total', 'Estado'];
            case 'all_sales':
            case 'delivery_sales':
                return ['Fecha | Hora', 'Caja', 'Cliente', 'Documento', 'Canal Venta', 'Tipo Pago', 'Total', 'Estado'];
            case 'sales_by_waiter':
            case 'sales_by_user':
                return ['Usuario', 'N° Órdenes', 'Total Ventas'];
            case 'payment_methods':
                return ['Forma de Pago', 'N° Operaciones', 'Total Ventas'];
            case 'all_purchases':
                return ['Fecha', 'Proveedor', 'Usuario', 'Total'];
            case 'purchases_by_supplier':
                return ['Proveedor', 'N° Compras', 'Total Compras'];
            case 'purchases_by_category':
                return ['Categoría', 'Cantidad', 'Total'];
            case 'cash_register':
                return ['Fecha', 'Caja', 'Usuario Apertura', 'Usuario Cierre', 'Saldo Inicial', 'Ventas', 'Saldo Final', 'Estado'];
            case 'profits':
                return ['Fecha', 'Producto', 'Cantidad Vendida', 'Costo Total', 'Venta Total', 'Ganancia'];
            case 'daily_closing':
                return ['Fecha', 'Caja', 'Total Ventas', 'Total Compras', 'Saldo Final'];
            case 'user_activity':
                return ['Usuario', 'Acción', 'Fecha | Hora', 'IP'];
            case 'system_logs':
                return ['Usuario', 'Acción', 'Descripción', 'Fecha | Hora', 'IP'];
            default:
                return [];
        }
    }
    
    private function formatRowData($item)
    {
        return $item; // Ya formateado en getReportDataForExport
    }
    
    private function getFilenameForReport()
    {
        $reportNames = [
            'products_by_channel' => 'productos_por_canal',
            'accounting_reports' => 'reportes_de_contabilidad',
            'all_sales' => 'todas_las_ventas',
            'delivery_sales' => 'ventas_delivery',
            'sales_by_waiter' => 'ventas_por_mesero',
            'sales_by_user' => 'ventas_por_usuario',
            'payment_methods' => 'metodos_de_pago',
            'all_purchases' => 'todas_las_compras',
            'purchases_by_supplier' => 'compras_por_proveedor',
            'purchases_by_category' => 'compras_por_categoria',
            'cash_register' => 'caja_registradora',
            'profits' => 'ganancias',
            'daily_closures' => 'cierres_diarios',
            'user_activity' => 'actividad_de_usuario',
            'system_logs' => 'logs_del_sistema'
        ];
        
        $reportName = $reportNames[$this->reportType] ?? 'reporte';
        return $reportName . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
    }
    
    private function getAllSalesData()
    {
        return $this->reportData->map(function ($order) {
            return [
                'created_at' => $order->order_datetime->format('d/m/Y H:i'),
                'cash_register' => $order->cashRegister->name ?? 'N/A',
                'customer' => $order->customer->name ?? ($order->client_name ?? 'Cliente no registrado'),
                'invoice_type' => $this->getInvoiceTypeLabel($order),
                'channel_label' => $this->getChannelLabel($order->service_type),
                'payment_method' => $this->getPaymentMethodLabel($order->payment_method),
                'total' => number_format($order->total, 2),
                'status' => $order->status,
            ];
        });
    }
    
    private function getSalesByWaiterData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'user_name' => $item->name,
                'orders_count' => $item->total_orders,
                'total_sales' => number_format($item->total_sales, 2),
            ];
        });
    }
    
    private function getSalesByUserData()
    {
        return $this->getSalesByWaiterData();
    }
    
    private function getPaymentMethodsData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'payment_method' => $this->getPaymentMethodLabel($item->payment_method),
                'operations_count' => $item->total_orders,
                'total_sales' => number_format($item->total_sales, 2),
            ];
        });
    }
    
    private function getAllPurchasesData()
    {
        return $this->reportData->map(function ($purchase) {
            return [
                'created_at' => $purchase->purchase_date->format('d/m/Y'),
                'supplier' => $purchase->supplier->business_name,
                'user' => $purchase->creator->name,
                'total' => number_format($purchase->total, 2),
            ];
        });
    }
    
    private function getPurchasesBySupplierData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'supplier' => $item->supplier_name,
                'purchases_count' => $item->total_purchases,
                'total_purchases' => number_format($item->total_amount, 2),
            ];
        });
    }
    
    private function getPurchasesByCategoryData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'category' => $item->category_name,
                'quantity' => $item->total_quantity,
                'total' => number_format($item->total_amount, 2),
            ];
        });
    }
    
    private function getCashRegisterData()
    {
        return $this->reportData->map(function ($cashRegister) {
            return [
                'date' => $cashRegister->opening_datetime->format('d/m/Y H:i'),
                'cash_register' => $cashRegister->name,
                'opening_user' => $cashRegister->openedBy->name,
                'closing_user' => $cashRegister->closedBy->name ?? 'Abierta',
                'initial_balance' => number_format($cashRegister->initial_balance, 2),
                'sales' => number_format($cashRegister->total_sales, 2),
                'final_balance' => number_format($cashRegister->final_balance, 2),
                'status' => $cashRegister->status,
            ];
        });
    }
    
    private function getProfitsData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'date' => $item->date,
                'product' => 'Resumen',
                'quantity_sold' => $item->total_orders,
                'total_cost' => 'N/A',
                'total_sale' => number_format($item->total_sales, 2),
                'profit' => 'N/A',
            ];
        });
    }
    
    private function getDailyClosuresData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'date' => $item->closing_datetime->format('d/m/Y H:i'),
                'cash_register' => $item->name,
                'total_sales' => number_format($item->total_sales, 2),
                'total_purchases' => number_format($item->total_purchases, 2),
                'final_balance' => number_format($item->final_balance, 2),
            ];
        });
    }
    
    private function getUserActivityData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'user' => $item->name,
                'action' => 'Actividad',
                'created_at' => $item->last_login_at ? $item->last_login_at->format('d/m/Y H:i') : 'Nunca',
                'ip_address' => 'N/A',
            ];
        });
    }
    
    private function getSystemLogsData()
    {
        return $this->reportData->map(function ($item) {
            return [
                'user' => $item->user,
                'action' => $item->event,
                'description' => $item->description,
                'created_at' => $item->date,
                'ip_address' => 'N/A',
            ];
        });
    }
    
    private function getChannelLabel($serviceType)
    {
        return match ($serviceType) {
            'table' => '🪑 Mesa',
            'delivery' => '🚚 Delivery',
            'pickup' => '📦 Para llevar',
            'mesa' => '🪑 Mesa',
            'llevar' => '📦 Para llevar',
            default => '❓ ' . ucfirst($serviceType)
        };
    }
    
    private function getInvoiceTypeLabel($order)
    {
        if (!$order->invoices || $order->invoices->isEmpty()) {
            return 'Sin comprobante';
        }
        
        $invoice = $order->invoices->first();
        return match ($invoice->invoice_type) {
            'invoice' => 'Factura',
            'receipt' => $invoice->sunat_status ? 'Boleta' : 'Nota de venta',
            'sales_note' => 'Nota de venta',
            default => 'Desconocido'
        };
    }
    
    private function getPaymentMethodLabel($method)
    {
        return match ($method) {
            'cash' => '💵 Efectivo',
            'card' => '💳 Tarjeta',
            'transfer' => '🏦 Transferencia',
            'yape' => '📱 Yape',
            'plin' => '📱 Plin',
            'efectivo' => '💵 Efectivo',
            'tarjeta' => '💳 Tarjeta',
            'transferencia' => '🏦 Transferencia',
            default => '❓ ' . ucfirst($method)
        };
    }

    public function getTitle(): string
    {
        if (empty($this->reportType)) {
            return 'Reporte';
        }
        
        return match ($this->reportType) {
            // SALES REPORTS
            'all_sales' => 'Reporte de Todas las Ventas',
            'delivery_sales' => 'Reporte de Ventas por Delivery',
            'sales_by_waiter' => 'Reporte de Ventas por Mesero',
            'products_by_channel' => 'Reporte de Ganancia por Canal de Venta',
            'payment_methods' => 'Reporte de Formas de Pago',
            
            // PURCHASES REPORTS
            'all_purchases' => 'Reporte de Todas las Compras',
            'purchases_by_supplier' => 'Reporte de Compras por Proveedor',
            'purchases_by_category' => 'Reporte de Compras por Categoría',
            
            // FINANCE REPORTS
            'cash_register' => 'Reporte de Movimientos de Caja',
            'profits' => 'Reporte de Ganancias',
            'daily_closing' => 'Reporte de Cierres Diarios',
            'accounting_reports' => 'Reportes de Contabilidad',
            
            // OPERATIONS REPORTS
            'sales_by_user' => 'Reporte de Ventas por Usuario',
            'user_activity' => 'Reporte de Actividad de Usuarios',
            'system_logs' => 'Reporte de Logs del Sistema',
            
            default => 'Reporte'
        };
    }

    // MÉTODOS PARA REPORTES DE COMPRAS SIN FILTROS DE FECHA
    protected function getAllPurchasesWithoutFilters()
    {
        return \App\Models\Purchase::with(['supplier', 'creator'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }
    
    protected function getPurchasesBySupplierWithoutFilters()
    {
        return \App\Models\Purchase::join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.business_name as supplier_name',
                \DB::raw('COUNT(purchases.id) as total_purchases'),
                \DB::raw('SUM(purchases.total) as total_amount')
            )
            ->groupBy('suppliers.id', 'suppliers.business_name')
            ->orderBy('suppliers.business_name')
            ->get();
    }
    
    protected function getPurchasesByCategoryWithoutFilters()
    {
        return \App\Models\PurchaseDetail::join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->select(
                'product_categories.name as category_name',
                \DB::raw('SUM(purchase_details.quantity) as total_quantity'),
                \DB::raw('SUM(purchase_details.subtotal) as total_amount')
            )
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('product_categories.name')
            ->get();
    }

    // MÉTODOS PARA REPORTES DE CONTABILIDAD
    protected function getAccountingReports($startDateTime, $endDateTime)
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("=", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET ACCOUNTING REPORTS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("PARÁMETROS:\n");
        ReportViewerPageLog::writeRaw("  startDateTime: " . $startDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  endDateTime: " . $endDateTime->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("  invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $query = \App\Models\Invoice::with(['customer', 'order'])
            ->whereBetween('issue_date', [$startDateTime->format('Y-m-d'), $endDateTime->format('Y-m-d')])
            // Por defecto, excluir Notas de Venta (series NV%)
            ->where('series', 'NOT LIKE', 'NV%');
        
        // Aplicar filtro por tipo de comprobante si está presente
        if ($this->invoiceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType: " . $this->invoiceType . "\n");
            // Usar el mismo método de filtrado que los otros reportes
            switch ($this->invoiceType) {
                case 'receipt':
                    $query->where('series', 'LIKE', 'B%');
                    break;
                case 'invoice':
                    $query->where('series', 'LIKE', 'F%');
                    break;
                default:
                    $query->where('invoice_type', $this->invoiceType);
                    break;
            }
        }
        
        $results = $query->orderBy('issue_date', 'desc')->orderBy('series')->orderBy('number')->get();
        
        ReportViewerPageLog::writeRaw("RESULTADOS: " . $results->count() . " registros\n");
        
        // DEBUG: Mostrar series encontradas para verificar que no haya NV%
        $seriesEncontradas = $results->pluck('series')->unique()->values();
        ReportViewerPageLog::writeRaw("SERIES ENCONTRADAS: " . json_encode($seriesEncontradas->toArray()) . "\n");
        
        // Contar por invoice_type para debugging
        $tiposContados = [];
        $results->each(function ($item) use (&$tiposContados) {
            $tiposContados[$item->invoice_type] = ($tiposContados[$item->invoice_type] ?? 0) + 1;
        });
        
        ReportViewerPageLog::writeRaw("CONTEO POR invoice_type:\n");
        foreach ($tiposContados as $tipo => $cantidad) {
            ReportViewerPageLog::writeRaw("  $tipo: $cantidad\n");
        }
        
        ReportViewerPageLog::writeRaw("=== GET ACCOUNTING REPORTS - FIN ===\n");
        
        return $results;
    }
    
    protected function getAccountingReportsWithoutFilters()
    {
        ReportViewerPageLog::writeRaw("\n" . str_repeat("=", 60) . "\n");
        ReportViewerPageLog::writeRaw("=== GET ACCOUNTING REPORTS WITHOUT FILTERS - INICIO ===\n");
        ReportViewerPageLog::writeRaw("TIMESTAMP: " . now()->format('Y-m-d H:i:s') . "\n");
        ReportViewerPageLog::writeRaw("invoiceType: " . ($this->invoiceType ?? 'null') . "\n");
        
        $query = \App\Models\Invoice::with(['customer', 'order'])
            // Por defecto, excluir Notas de Venta (series NV%)
            ->where('series', 'NOT LIKE', 'NV%');
        
        // Aplicar filtro por tipo de comprobante si está presente
        if ($this->invoiceType) {
            ReportViewerPageLog::writeRaw("APLICANDO FILTRO invoiceType: " . $this->invoiceType . "\n");
            // Usar el mismo método de filtrado que los otros reportes
            switch ($this->invoiceType) {
                case 'receipt':
                    $query->where('series', 'LIKE', 'B%');
                    break;
                case 'invoice':
                    $query->where('series', 'LIKE', 'F%');
                    break;
                default:
                    $query->where('invoice_type', $this->invoiceType);
                    break;
            }
        }
        
        $results = $query->orderBy('issue_date', 'desc')->orderBy('series')->orderBy('number')->get();
        
        ReportViewerPageLog::writeRaw("RESULTADOS: " . $results->count() . " registros\n");
        
        // DEBUG: Mostrar series encontradas para verificar que no haya NV%
        $seriesEncontradas = $results->pluck('series')->unique()->values();
        ReportViewerPageLog::writeRaw("SERIES ENCONTRADAS: " . json_encode($seriesEncontradas->toArray()) . "\n");
        
        // Contar por invoice_type para debugging
        $tiposContados = [];
        $results->each(function ($item) use (&$tiposContados) {
            $tiposContados[$item->invoice_type] = ($tiposContados[$item->invoice_type] ?? 0) + 1;
        });
        
        ReportViewerPageLog::writeRaw("CONTEO POR invoice_type:\n");
        foreach ($tiposContados as $tipo => $cantidad) {
            ReportViewerPageLog::writeRaw("  $tipo: $cantidad\n");
        }
        
        ReportViewerPageLog::writeRaw("=== GET ACCOUNTING REPORTS WITHOUT FILTERS - FIN ===\n");
        
        return $results;
    }
    
    /**
     * Obtiene el tipo de comprobante formateado para reportes de contabilidad
     */
    private function getInvoiceTypeLabelForAccounting($invoice): string
    {
        // DEBUG: Log para depurar la clasificación
        ReportViewerPageLog::writeRaw("DEBUG CLASIFICACIÓN:\n");
        ReportViewerPageLog::writeRaw("  ID: " . $invoice->id . "\n");
        ReportViewerPageLog::writeRaw("  Serie: " . $invoice->series . "\n");
        ReportViewerPageLog::writeRaw("  invoice_type: " . $invoice->invoice_type . "\n");
        ReportViewerPageLog::writeRaw("  sunat_status: " . ($invoice->sunat_status ?? 'null') . "\n");
        
        $resultado = match($invoice->invoice_type) {
            'invoice' => 'Factura',
            'receipt' => $invoice->sunat_status ? 'Boleta' : 'Nota de Venta',
            'sales_note' => 'Nota de Venta',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            default => 'Desconocido'
        };
        
        ReportViewerPageLog::writeRaw("  Resultado clasificación: $resultado\n");
        
        return $resultado;
    }
    
    /**
     * Obtiene el estado del comprobante formateado
     */
    private function getInvoiceStatusLabel($invoice): string
    {
        if ($invoice->tax_authority_status === 'voided') {
            return 'Anulado';
        }
        
        return match($invoice->sunat_status) {
            null => 'Pendiente',
            'PENDIENTE' => 'Pendiente',
            'ACEPTADO' => 'Aceptado',
            'RECHAZADO' => 'Rechazado',
            'OBSERVADO' => 'Observado',
            'NO_APLICA' => 'No aplica',
            default => $invoice->tax_authority_status ?? 'Desconocido'
        };
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
    
    /**
     * Obtiene el número de documento del cliente para reportes de contabilidad
     */
    private function getCustomerDocumentNumberForAccounting($invoice): string
    {
        // Si no hay cliente, retornar vacío
        if (!$invoice->customer) {
            return '';
        }
        
        // Obtener el tipo de comprobante para determinar qué documento mostrar
        $invoiceTypeLabel = $this->getInvoiceTypeLabelForAccounting($invoice);
        
        // Si es Boleta, mostrar DNI
        if ($invoiceTypeLabel === 'Boleta') {
            return $invoice->customer->document_type === 'DNI' ? $invoice->customer->document_number : '';
        }
        
        // Si es Factura, mostrar RUC
        if ($invoiceTypeLabel === 'Factura') {
            return $invoice->customer->document_type === 'RUC' ? $invoice->customer->document_number : '';
        }
        
        // Para otros tipos, mostrar el documento si existe
        return $invoice->customer->document_number ?? '';
    }
}
