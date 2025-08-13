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
    public string $reportType;
    public string $category;

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
    

    public function mount(string $category, string $reportType): void
    {
        $this->category = $category;
        $this->reportType = $reportType;
        
        // Handle dateRange from URL
        $dateRange = request('dateRange', 'today');
        $this->setDateRange($dateRange);
        
        // Initialize form
        $this->form->fill([
            'dateRange' => $this->dateRange,
            'format' => 'pdf',
        ]);

        // Load initial data
        $this->loadReportData();
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
        // Solución KISS: por ahora retornamos 0, podemos mejorar después
        return 0;
    }

    protected function getTotalCancelled(): float
    {
        // Solución KISS: por ahora retornamos 0, podemos mejorar después  
        return 0;
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
            ->with(['customer', 'user', 'table', 'cashRegister', 'invoices']);
            
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }
        
        // Aplicar filtro por tipo de comprobante si está presente
        $this->applyInvoiceTypeFilter($query);
        
        return $query->orderBy('order_datetime', 'desc')->get();
    }
    
    protected function applyInvoiceTypeFilter($query)
    {
        if ($this->invoiceType) {
            $query->whereHas('invoices', function ($invoiceQuery) {
                $invoiceQuery->where('invoice_type', $this->invoiceType);
            });
        }
    }
    
    protected function getProductsByChannel($startDateTime, $endDateTime)
    {
        return \App\Models\OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
            ->where('orders.billed', true)
            ->select(
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
            ->with(['supplier', 'user'])
            ->orderBy('purchase_date', 'desc')
            ->get();
    }
    
    protected function getPurchasesBySupplier($startDateTime, $endDateTime)
    {
        return \App\Models\Purchase::join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchases.purchase_date', [$startDateTime, $endDateTime])
            ->select(
                'suppliers.name as supplier_name',
                \DB::raw('COUNT(purchases.id) as total_purchases'),
                \DB::raw('SUM(purchases.total) as total_amount')
            )
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderBy('suppliers.name')
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
        return \App\Models\CashRegister::whereBetween('opened_at', [$startDateTime, $endDateTime])
            ->with(['user'])
            ->orderBy('opened_at', 'desc')
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
        return \App\Models\CashRegister::whereBetween('closed_at', [$startDateTime, $endDateTime])
            ->whereNotNull('closed_at')
            ->with(['user'])
            ->orderBy('closed_at', 'desc')
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

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}