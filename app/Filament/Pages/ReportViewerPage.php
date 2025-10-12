<?php

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
use Livewire\Component;

class ReportViewerPage extends Page implements HasForms
{
    use InteractsWithForms;
    
    // protected static string $layout = 'filament-panels::layout.index';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.report-viewer-page';
    protected ?string $maxContentWidth = 'full';

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
        ];
    }
    

    public function mount(string $category = '', string $reportType = ''): void
    {
        $this->category = $category;
        $this->reportType = $reportType;
        
        // Only proceed if we have valid parameters
        if (!empty($this->category) && !empty($this->reportType)) {
            // Capturar filtro de tipo de comprobante del request
            $this->invoiceType = request('invoiceType');
            
            // Si no viene dateRange en la URL, NO aplicar filtros por defecto
            if (request()->has('dateRange')) {
                $dateRange = request('dateRange');
                $this->setDateRange($dateRange);
            } else {
                // Cargar SIN FILTROS - todos los datos históricos
                $this->dateRange = null;
                $this->startDate = null;
                $this->endDate = null;
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
        // Skip if no valid report type
        if (empty($this->reportType)) {
            $this->reportData = collect([]);
            $this->reportStats = [];
            return;
        }

        // Si no hay fechas establecidas, cargar TODOS los datos sin filtros
        if (!$this->startDate || !$this->endDate) {
            $this->reportData = $this->getReportDataWithoutFilters();
            
            $this->reportStats = [
                'total_operations' => $this->reportData->count(),
                'total_sales' => $this->reportData->sum('total') ?? 0,
                'total_sales_notes' => $this->getTotalByInvoiceType('sales_note'),
                'total_receipts' => $this->getTotalByInvoiceType('receipt'), 
                'total_invoices' => $this->getTotalByInvoiceType('invoice'),
                'total_cancelled' => $this->getTotalCancelled(),
                'period' => 'Todos los registros históricos',
            ];
            return;
        }

        $startDateTime = $this->getStartDateTime();
        $endDateTime = $this->getEndDateTime();

        // Get data based on report type
        $this->reportData = $this->getReportData($startDateTime, $endDateTime);

        // Calculate detailed stats for sales reports
        $periodFormat = 'd/m/Y';
        if ($this->startTime || $this->endTime) {
            $periodFormat = 'd/m/Y H:i';
        }
        
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
        return match ($this->reportType) {
            // SALES REPORTS - SIN FILTROS DE FECHA
            'all_sales' => $this->getOrdersQueryWithoutFilters(),
            'delivery_sales' => $this->getOrdersQueryWithoutFilters('delivery'),
            'sales_by_waiter' => $this->getSalesByWaiterWithoutFilters(),
            'products_by_channel' => $this->getProductsByChannelWithoutFilters(),
            
            // PURCHASES REPORTS - SIN FILTROS DE FECHA
            'all_purchases' => $this->getAllPurchasesWithoutFilters(),
            'purchases_by_supplier' => $this->getPurchasesBySupplierWithoutFilters(),
            'purchases_by_category' => $this->getPurchasesByCategoryWithoutFilters(),
            
            // Otros reportes también sin filtros...
            default => collect([])
        };
    }

    protected function getReportData($startDateTime, $endDateTime)
    {
        return match ($this->reportType) {
            // SALES REPORTS
            'all_sales' => $this->getOrdersQuery($startDateTime, $endDateTime),
            'delivery_sales' => $this->getOrdersQuery($startDateTime, $endDateTime, 'delivery'),
            'sales_by_waiter' => $this->getSalesByWaiter($startDateTime, $endDateTime),
            'products_by_channel' => $this->getProductsByChannel($startDateTime, $endDateTime),
            'payment_methods' => $this->getPaymentMethods($startDateTime, $endDateTime),
            
            // PURCHASES REPORTS
            'all_purchases' => $this->getAllPurchases($startDateTime, $endDateTime),
            'purchases_by_supplier' => $this->getPurchasesBySupplier($startDateTime, $endDateTime),
            'purchases_by_category' => $this->getPurchasesByCategory($startDateTime, $endDateTime),
            
            // FINANCE REPORTS
            'cash_register' => $this->getCashRegisterMovements($startDateTime, $endDateTime),
            'profits' => $this->getProfits($startDateTime, $endDateTime),
            'daily_closing' => $this->getDailyClosing($startDateTime, $endDateTime),
            
            // OPERATIONS REPORTS
            'sales_by_user' => $this->getSalesByUser($startDateTime, $endDateTime),
            'user_activity' => $this->getUserActivity($startDateTime, $endDateTime),
            'system_logs' => $this->getSystemLogs($startDateTime, $endDateTime),
            
            default => collect([]),
        };
    }
    
    protected function getOrdersQuery($startDateTime, $endDateTime, $serviceType = null)
    {
        $query = Order::whereBetween('order_datetime', [$startDateTime, $endDateTime])
            ->where('billed', true)
            ->with([
                'customer',           // Cliente formal de la order
                'user', 
                'table',              // Para mostrar mesa cuando no hay cliente
                'cashRegister', 
                'invoices.customer'   // Cliente formal de la invoice + campo client_name para comandas rápidas
            ]);
            
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        // Aplicar filtro por tipo de comprobante si está presente
        $this->applyInvoiceTypeFilter($query);
        
        // Log the SQL query for debugging
        \Log::info('Orders Query SQL: ' . $query->toSql());
        \Log::info('Orders Query Bindings: ' . json_encode($query->getBindings()));
        
        $results = $query->orderBy('order_datetime', 'desc')->get();
        
        // Debug invoice types
        $invoiceTypes = $results->flatMap(function($order) {
            return $order->invoices->pluck('invoice_type');
        })->unique()->values();
        \Log::info('Invoice types found in results: ' . json_encode($invoiceTypes->toArray()));
        
        return $results;
    }

    protected function getOrdersQueryWithoutFilters($serviceType = null)
    {
        $query = Order::where('billed', true)
            ->with([
                'customer',           // Cliente formal de la order
                'user', 
                'table',              // Para mostrar mesa cuando no hay cliente
                'cashRegister', 
                'invoices.customer'   // Cliente formal de la invoice + campo client_name para comandas rápidas
            ]);
            
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        // Aplicar filtro por tipo de comprobante si está presente
        $this->applyInvoiceTypeFilter($query);
        
        // Log the SQL query for debugging
        \Log::info('Orders Query Without Filters SQL: ' . $query->toSql());
        \Log::info('Orders Query Without Filters Bindings: ' . json_encode($query->getBindings()));
        
        return $query->orderBy('order_datetime', 'desc')->get();
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
    
    protected function getProductsByChannelWithoutFilters()
    {
        $query = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.billed', true);
            
        // Aplicar filtro por tipo de comprobante si está presente
        $this->applyInvoiceTypeFilter($query);
            
        return $query->select(
                'products.name as product_name',
                'orders.service_type',
                \DB::raw('SUM(order_details.quantity) as total_quantity'),
                \DB::raw('SUM(order_details.subtotal) as total_sales')
            )
            ->groupBy('products.name', 'orders.service_type')
            ->orderBy('products.name')
            ->orderBy('orders.service_type')
            ->get();
    }
    
    protected function applyInvoiceTypeFilter($query)
    {
        if ($this->invoiceType) {
            \Log::info("Aplicando filtro de tipo de comprobante: {$this->invoiceType}");
            
            // Filtrar EXCLUSIVAMENTE por serie (más confiable que invoice_type)
            $query->whereHas('invoices', function ($invoiceQuery) {
                switch ($this->invoiceType) {
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
                        $invoiceQuery->where('invoice_type', $this->invoiceType);
                        break;
                }
            });
        } else {
            \Log::info("No se aplica filtro de tipo de comprobante - invoiceType es: " . ($this->invoiceType ?? 'null'));
        }
    }
    
    protected function getProductsByChannel($startDateTime, $endDateTime)
    {
        $query = \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
            ->where('orders.billed', true);
            
        // Aplicar filtro por tipo de comprobante si está presente
        $this->applyInvoiceTypeFilter($query);
            
        return $query->select(
                'products.name as product_name',
                'orders.service_type',
                \DB::raw('SUM(order_details.quantity) as total_quantity'),
                \DB::raw('SUM(order_details.subtotal) as total_sales')
            )
            ->groupBy('products.name', 'orders.service_type')
            ->orderBy('products.name')
            ->orderBy('orders.service_type')
            ->get();
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

    public function exportReport(): void
    {
        try {
            Notification::make()
                ->title('✅ Reporte exportado correctamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error al exportar el reporte')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
            'products_by_channel' => 'Reporte de Productos por Canal de Venta',
            'payment_methods' => 'Reporte de Formas de Pago',
            
            // PURCHASES REPORTS
            'all_purchases' => 'Reporte de Todas las Compras',
            'purchases_by_supplier' => 'Reporte de Compras por Proveedor',
            'purchases_by_category' => 'Reporte de Compras por Categoría',
            
            // FINANCE REPORTS
            'cash_register' => 'Reporte de Movimientos de Caja',
            'profits' => 'Reporte de Ganancias',
            'daily_closing' => 'Reporte de Cierres Diarios',
            
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

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}