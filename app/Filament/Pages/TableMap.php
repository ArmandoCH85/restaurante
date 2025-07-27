<?php

namespace App\Filament\Pages;

use App\Models\Table;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms;
use App\Filament\Pages\PosInterface;
use Illuminate\Support\Facades\DB;
use App\Models\CashRegister;

class TableMap extends Page
{
    // Propiedades básicas para la navegación
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Mapa de Mesas';
    protected static ?string $title = 'Mapa de Mesas';

    // IMPORTANTE: Slug debe coincidir con la ruta esperada
    protected static ?string $slug = 'mapa-mesas';
    protected static ?string $navigationGroup = '🏪 Operaciones Diarias';
    protected static ?int $navigationSort = 2;

    // Nueva vista 100% Filament nativo
    protected static string $view = 'filament.pages.table-map-filament-native';

    // PROPIEDADES PARA EL MAPA DE MESAS
    public $tables;
    public $selectedTable = null;
    public $deliveryOrders;
    public $selectedDeliveryOrder = null;

    // Filtros
    public $statusFilter = null;
    public $locationFilter = null;
    public $searchQuery = null;
    public $showTodayReservations = false;
    public $showDeliveryOrders = false;
    public $floorFilter = null;
    public $capacityFilter = null;

    // Vista
    public $viewMode = 'grid'; // grid o layout
    public $isEditingLayout = false;
    public $filtersOpen = false;

    // Estadísticas
    public $availableCount = 0;
    public $occupiedCount = 0;
    public $reservedCount = 0;
    public $preBillCount = 0;
    public $maintenanceCount = 0;

    // Datos adicionales
    public $floors;

    // Modales
    public $showQrModal = false;
    public $qrTableId = null;

    public function mount(): void
    {
        $this->loadFloors();
        $this->loadTables();
        $this->loadDeliveryOrders();
        $this->calculateStats();
    }

    public function loadFloors(): void
    {
        // Simulando pisos básicos - puedes ajustar según tu modelo
        $this->floors = collect([
            (object)['id' => 1, 'name' => 'Planta Baja'],
            (object)['id' => 2, 'name' => 'Primer Piso'],
        ]);
    }

    public function loadTables(): void
    {
        $query = Table::query();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->locationFilter) {
            $query->where('location', $this->locationFilter);
        }

        if ($this->floorFilter) {
            // Si tienes un campo floor_id en tu tabla, úsalo
            // $query->where('floor_id', $this->floorFilter);
        }

        if ($this->capacityFilter) {
            $query->where('capacity', '>=', $this->capacityFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('number', 'like', "%{$this->searchQuery}%")
                    ->orWhere('location', 'like', "%{$this->searchQuery}%");
            });
        }

        $query->with(['activeReservations' => function ($query) {
            $query->whereDate('reservation_date', Carbon::today());
        }]);

        if ($this->showTodayReservations) {
            $query->whereHas('activeReservations', function ($query) {
                $query->whereDate('reservation_date', Carbon::today());
            });
        }

        $this->tables = $query->orderBy('location')->orderBy('number')->get();
        $this->calculateStats();
    }

    public function calculateStats(): void
    {
        $this->availableCount = $this->tables->where('status', 'available')->count();
        $this->occupiedCount = $this->tables->where('status', 'occupied')->count();
        $this->reservedCount = $this->tables->where('status', 'reserved')->count();
        
        // Agregar estadísticas para nuevos estados POS
        $this->preBillCount = $this->tables->whereIn('status', ['pending_payment', 'prebill'])->count();
        $this->maintenanceCount = $this->tables->where('status', 'maintenance')->count();
    }

    // 🚚 MÉTODO PARA OBTENER ESTADÍSTICAS DE DELIVERY
    public function getDeliveryStats(): array
    {
        $pendingCount = DeliveryOrder::where('status', 'pending')->count();
        $assignedCount = DeliveryOrder::where('status', 'assigned')->count();
        $inTransitCount = DeliveryOrder::where('status', 'in_transit')->count();
        $todayDeliveries = DeliveryOrder::whereDate('created_at', today())->count();

        return [
            'pending' => $pendingCount,
            'assigned' => $assignedCount,
            'in_transit' => $inTransitCount,
            'today_total' => $todayDeliveries,
        ];
    }

    public function loadDeliveryOrders(): void
    {
        $query = DeliveryOrder::with(['order.customer', 'deliveryPerson'])
            ->whereIn('status', ['pending', 'assigned', 'in_transit']);

        $user = Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        if ($isDeliveryPerson) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $query->where('delivery_person_id', $employee->id);
            }
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('delivery_address', 'like', "%{$this->searchQuery}%")
                    ->orWhereHas('order.customer', function ($sq) {
                        $sq->where('name', 'like', "%{$this->searchQuery}%")
                            ->orWhere('phone', 'like', "%{$this->searchQuery}%");
                    });
            });
        }

        $this->deliveryOrders = $query->orderBy('created_at', 'desc')->get();
    }

    // ACCIÓN PARA SELECCIONAR LA CUENTA A GESTIONAR
    public function selectOrderToManageAction(): Action
    {
        return Action::make('selectOrderToManage')
            ->label('Seleccionar Cuenta')
            ->modalHeading('Múltiples Cuentas Abiertas')
            ->modalDescription('Esta mesa tiene varias cuentas. Por favor, selecciona cuál deseas gestionar.')
            ->modalWidth('md')
            ->modalSubmitAction(false) // Ocultar botón de "Aceptar" por defecto
            ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
            ->form(function (array $arguments) {
                $table = Table::find($arguments['tableId'] ?? null);
                if (!$table) return [];

                $buttons = [];
                foreach ($table->openOrders as $index => $order) {
                    $label = $order->parent_id
                        ? sprintf('Cuenta Separada #%d (Total: S/. %s)', $order->id, number_format($order->total, 2))
                        : sprintf('Cuenta Principal #%d (Total: S/. %s)', $order->id, number_format($order->total, 2));

                    $buttons[] = \Filament\Forms\Components\Actions\Action::make('select_order_' . $order->id)
                        ->label($label)
                        ->button()
                        ->color('primary')
                        ->action(fn() => $this->goToPos($table->id, $order->id));
                }

                return [
                    Forms\Components\Actions::make($buttons)->fullWidth()
                ];
            });
    }

    // Método para actualización automática cada 5 segundos
    public function refreshData(): void
    {
        $this->loadTables();
        $this->loadDeliveryOrders();
        $this->calculateStats();
    }

    // Métodos para actualizar filtros
    public function updatedStatusFilter(): void
    {
        $this->loadTables();
    }

    public function updatedLocationFilter(): void
    {
        $this->loadTables();
    }

    public function updatedFloorFilter(): void
    {
        $this->loadTables();
    }

    public function updatedCapacityFilter(): void
    {
        $this->loadTables();
    }

    public function updatedSearchQuery(): void
    {
        $this->loadTables();
        $this->loadDeliveryOrders();
    }

    public function updatedShowTodayReservations(): void
    {
        $this->loadTables();
    }

    public function updatedShowDeliveryOrders(): void
    {
        if ($this->showDeliveryOrders) {
            $this->loadDeliveryOrders();
        } else {
            $this->deliveryOrders = collect([]);
        }
    }

    public function resetFilters(): void
    {
        $this->statusFilter = null;
        $this->locationFilter = null;
        $this->floorFilter = null;
        $this->capacityFilter = null;
        $this->searchQuery = null;
        $this->showTodayReservations = false;
        $this->loadTables();
    }

    // 🔴 MÉTODOS PARA BOTONES DE VISTA
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'layout' : 'grid';
    }

    public function setViewMode($mode): void
    {
        $this->viewMode = $mode;
    }

    public function toggleEditLayout(): void
    {
        $this->isEditingLayout = !$this->isEditingLayout;
    }

    public function resetLayout(): void
    {
        // Aquí puedes implementar la lógica para resetear el layout
        Notification::make()
            ->title('Layout reseteado')
            ->success()
            ->send();
    }

    public function toggleFilters(): void
    {
        $this->filtersOpen = !$this->filtersOpen;
    }

    public function resetAllTables(): void
    {
        // Resetear todas las mesas a disponible
        Table::query()->update(['status' => 'available']);

        $this->loadTables();

        Notification::make()
            ->title('Todas las mesas han sido reseteadas a disponible')
            ->success()
            ->send();
    }

    // Métodos para manejo de mesas
    public function selectTable($tableId): void
    {
        $this->selectedTable = Table::findOrFail($tableId);
    }

    public function unselectTable(): void
    {
        $this->selectedTable = null;
    }

    public function getStatusOptions(): array
    {
        return [
            'available' => '🟢 Disponible',
            'occupied' => '🔴 Ocupada',
            'reserved' => '🟡 Reservada',
            'pending_payment' => '🔵 Pre-Cuenta',
            'prebill' => '🔵 Pre-Factura',
            'maintenance' => '⚫ Mantenimiento',
        ];
    }

    public function getLocationOptions(): array
    {
        return [
            'interior' => 'Interior',
            'exterior' => 'Exterior',
            'terraza' => 'Terraza',
        ];
    }

    public function showTableDetails($tableId): void
    {
        $this->selectedTable = Table::findOrFail($tableId);
    }

    public function showQrCode($tableId): void
    {
        $this->qrTableId = $tableId;
        $this->showQrModal = true;
    }

    public function handleTableStatusUpdate(): void
    {
        $this->loadTables();
        if ($this->selectedTable) {
            $this->selectedTable = Table::find($this->selectedTable->id);
            if (!$this->selectedTable) {
                $this->selectedTable = null;
            }
        }
    }

    public function getTableMapData()
    {
        // Agrupar las mesas por ubicación para el mapa
        $groupedTables = $this->tables->groupBy('location');

        // Nombres de ubicaciones para mostrar en la interfaz
        $locationNames = $this->getLocationOptions();

        return [
            'groupedTables' => $groupedTables,
            'locationNames' => $locationNames,
            'showTableDetails' => (bool) $this->selectedTable,
            'selectedTable' => $this->selectedTable,
            'showQrModal' => $this->showQrModal,
            'qrTableId' => $this->qrTableId,
            'showDeliveryOrders' => $this->showDeliveryOrders,
            'deliveryOrders' => $this->deliveryOrders,
            'selectedDeliveryOrder' => $this->selectedDeliveryOrder,
            'statusOptions' => $this->getStatusOptions(),
            'deliveryStats' => $this->getDeliveryStats(), // 🚚 Nuevas estadísticas
        ];
    }

    /**
     * 🔴 PASO 1: Abrir Venta Directa para una mesa específica
     * Método que maneja el click en las tarjetas de mesa
     */
    public function openPOS(int $tableId): void
    {
        $table = Table::with('openOrders')->find($tableId);

        if (!$table) {
            Notification::make()->title('Error')->body('Mesa no encontrada.')->danger()->send();
            return;
        }

        // Si la mesa está disponible, redirigir y crear una nueva orden
        if ($table->status === 'available') {
            $this->goToPos($table->id);
            return;
        }

        // Si está ocupada, verificar el número de órdenes abiertas
        $openOrdersCount = $table->openOrders->count();

        if ($openOrdersCount === 0) {
            // No hay órdenes abiertas, pero la mesa está ocupada (estado inconsistente?).
            // Por seguridad, permitir crear una nueva orden.
            $this->goToPos($table->id);
        } elseif ($openOrdersCount === 1) {
            // Solo hay una orden, ir directamente a ella
            $this->goToPos($table->id, $table->openOrders->first()->id);
        } else {
            // Hay múltiples órdenes, mostrar el modal de selección
            $this->mountAction('selectOrderToManage', ['tableId' => $table->id]);
        }
    }

    public function goToPos(int $tableId, ?int $orderId = null): void
    {
        // 🔍 DEBUGGING: Agregar logs para ver qué está pasando
        Log::info('🎯 goToPos llamado', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_roles' => Auth::user()->roles->pluck('name')->toArray(),
            'table_id' => $tableId,
            'order_id' => $orderId,
        ]);

        $url = PosInterface::getUrl(
            ['table_id' => $tableId, 'order_id' => $orderId]
        );

        // 🔍 DEBUGGING: Ver la URL generada
        Log::info('🌐 URL generada para POS', [
            'url' => $url,
            'parameters' => ['table_id' => $tableId, 'order_id' => $orderId],
        ]);

        try {
            $this->redirect($url);
            Log::info('✅ Redirección ejecutada exitosamente');
        } catch (\Exception $e) {
            Log::error('❌ Error en redirección', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            Notification::make()
                ->title('Error de Navegación')
                ->body('No se pudo abrir el POS. Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Método para determinar si se debe mostrar la navegación
    public static function shouldRegisterNavigation(): bool
    {
        // Tu lógica de permisos aquí
        // Por ejemplo, solo mostrar si el usuario es admin o manager
        // return Auth::check() && Auth::user()->hasAnyRole(['admin', 'manager']);
        return true;
    }

    // Optimizar el ancho del contenido para aprovechar mejor el espacio
    public function getMaxContentWidth(): ?string
    {
        return 'full'; // Aprovechamos todo el ancho disponible para el mapa de mesas
    }

    // 🚚 MÉTODO PARA OBTENER LAS ACCIONES DEL HEADER - INCLUYE BOTÓN DE DELIVERY
    protected function getHeaderActions(): array
    {
        return [
            // 🚚 ACCIÓN PRINCIPAL: NUEVO DELIVERY
            $this->newDeliveryAction(),

            // Acción de actualización existente
            Action::make('refresh')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->refreshData();
                    Notification::make()
                        ->title('Datos actualizados')
                        ->success()
                        ->send();
                }),
        ];
    }

    // 🚚 FORMULARIO DE DELIVERY OPTIMIZADO CON MEJORES PRÁCTICAS DE FILAMENT
    protected function newDeliveryAction(): Action
    {
        return Action::make('newDelivery')
            ->label('📦 Nuevo Delivery')
            ->color('success')
            ->icon('heroicon-o-truck')
            ->size('lg')
            ->url('/admin/ventas/delivery')
            ->openUrlInNewTab(false);
    }

}
