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
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'Mantenimiento',
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
            ->modalWidth('7xl') // Más ancho para mejor UX
            ->modalHeading('🚚 Crear Pedido de Delivery')
            ->modalDescription('Complete los datos para crear un nuevo pedido de delivery')
            ->modalIcon('heroicon-o-truck')
            ->modalIconColor('success')
            ->slideOver() // Mejor UX que modal - slide over es más moderno
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->closeModalByClickingAway(false) // Prevenir cierre accidental
            ->form([
                // ============ PASO 1: CLIENTE (BÚSQUEDA POR TELÉFONO Y CREACIÓN) ============
                Forms\Components\Section::make('👤 Información del Cliente')
                    ->description('Busque el cliente por teléfono o créelo si no existe')
                    ->icon('heroicon-o-user')
                    ->compact() // Reducir espaciado
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                // Campo de búsqueda por teléfono
                                Forms\Components\TextInput::make('search_phone')
                                    ->label('🔍 Buscar por Teléfono')
                                    ->tel()
                                    ->placeholder('+51 999 999 999')
                                    ->helperText('Ingrese el teléfono para buscar al cliente')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        if (empty($state)) {
                                            $set('customer_id', null);
                                            $set('customer_found', false);
                                            $set('delivery_address', '');
                                            return;
                                        }

                                        // Buscar cliente por teléfono
                                        $customer = \App\Models\Customer::where('phone', 'LIKE', '%' . $state . '%')
                                            ->first();

                                        if ($customer) {
                                            $set('customer_id', $customer->id);
                                            $set('customer_found', true);
                                            $set('customer_name', $customer->name);
                                            $set('customer_document', $customer->document_type . ': ' . $customer->document_number);
                                            $set('customer_email', $customer->email);
                                            $set('delivery_address', $customer->address);
                                        } else {
                                            $set('customer_id', null);
                                            $set('customer_found', false);
                                            $set('customer_name', '');
                                            $set('customer_document', '');
                                            $set('customer_email', '');
                                        }
                                    })
                                    ->columnSpan(1),

                                // Select de cliente (respaldo)
                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente Existente')
                                    ->searchable(['phone', 'name', 'email'])
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return \App\Models\Customer::where('phone', 'like', "%{$search}%")
                                            ->orWhere('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($customer) {
                                                return [$customer->id => "{$customer->name} - {$customer->phone}"];
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $customer = \App\Models\Customer::find($value);
                                        return $customer ? $customer->name . ' - ' . $customer->phone : '';
                                    })
                                    ->placeholder('🔍 Buscar por teléfono, nombre o email...')
                                    ->helperText('O busque directamente aquí')
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if ($state) {
                                            $customer = \App\Models\Customer::find($state);
                                            if ($customer) {
                                                $set('search_phone', $customer->phone);
                                                $set('customer_found', true);
                                                $set('customer_name', $customer->name);
                                                $set('customer_document', $customer->document_type . ': ' . $customer->document_number);
                                                $set('customer_email', $customer->email);
                                                $set('delivery_address', $customer->address);
                                            }
                                        }
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Información del cliente encontrado
                        Forms\Components\Placeholder::make('customer_info')
                            ->label('Cliente Encontrado')
                            ->content(function (Forms\Get $get): string {
                                if (!$get('customer_found')) {
                                    return '❌ Cliente no encontrado. Complete los datos abajo para crear uno nuevo.';
                                }

                                return '✅ Cliente encontrado: ' . ($get('customer_name') ?? 'Sin nombre') .
                                    ' (' . ($get('customer_document') ?? 'Sin documento') . ')';
                            })
                            ->visible(fn(Forms\Get $get): bool => !empty($get('search_phone')))
                            ->extraAttributes(['class' => 'text-sm']),

                        // Campos para crear nuevo cliente (solo si no se encontró)
                        Forms\Components\Group::make([
                            Forms\Components\Grid::make([
                                'default' => 1,
                                'lg' => 2,
                            ])
                                ->schema([
                                    Forms\Components\TextInput::make('new_customer_name')
                                        ->label('Nombre Completo')
                                        ->placeholder('Ej: Juan Pérez')
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('new_customer_document_type')
                                        ->label('Tipo de Documento')
                                        ->options([
                                            'DNI' => 'DNI',
                                            'RUC' => 'RUC',
                                            'CE' => 'Carnet de Extranjería',
                                        ])
                                        ->default('DNI')
                                        ->native(false)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('new_customer_document_number')
                                        ->label('Número de Documento')
                                        ->placeholder('12345678')
                                        ->maxLength(15)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('new_customer_email')
                                        ->label('Email (Opcional)')
                                        ->email()
                                        ->placeholder('cliente@email.com')
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                ]),
                        ])
                            ->visible(fn(Forms\Get $get): bool => !$get('customer_found') && !empty($get('search_phone'))),
                    ]),

                // ============ PASO 2: DIRECCIÓN (COMPACTA) ============
                Forms\Components\Section::make('📍 Dirección de Entrega')
                    ->description('Información para la entrega')
                    ->icon('heroicon-o-map-pin')
                    ->compact() // Reducir espaciado
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                Forms\Components\Textarea::make('delivery_address')
                                    ->label('Dirección Completa')
                                    ->required()
                                    ->rows(2)
                                    ->placeholder('Av. Principal 123, Distrito, Ciudad')
                                    ->helperText('Sea específico: calle, número, urbanización, distrito')
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('delivery_references')
                                    ->label('Referencias de Ubicación')
                                    ->rows(2)
                                    ->placeholder('Frente al parque, casa azul, portón negro, etc.')
                                    ->helperText('Puntos de referencia para encontrar fácilmente')
                                    ->columnSpan(1),
                            ]),
                    ]),

                // ============ PASO 3: PRODUCTOS (REPEATER OPTIMIZADO) ============
                Forms\Components\Section::make('🍽️ Productos del Pedido')
                    ->description('Seleccione los productos y cantidades')
                    ->icon('heroicon-o-shopping-bag')
                    ->compact() // Reducir espaciado
                    ->schema([
                        Forms\Components\Repeater::make('products')
                            ->schema([
                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'sm' => 4,
                                ])
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Producto')
                                            ->options(
                                                \App\Models\Product::where('active', true)
                                                    ->where('available', true)
                                                    ->whereIn('product_type', ['sale_item', 'both'])
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                if ($state) {
                                                    $product = \App\Models\Product::find($state);
                                                    if ($product) {
                                                        $set('unit_price', $product->price);
                                                        $set('product_name', $product->name);
                                                    }
                                                }
                                            })
                                            ->columnSpan([
                                                'default' => 1,
                                                'sm' => 2,
                                            ]),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(999)
                                            ->required()
                                            ->live()
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Precio Unit.')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Observaciones (Opcional)')
                                    ->rows(1)
                                    ->placeholder('Especificaciones del producto...')
                                    ->columnSpanFull(),
                            ])
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                            )
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (!isset($state['product_name'], $state['quantity'])) {
                                    return 'Producto sin configurar';
                                }
                                return "{$state['product_name']} x{$state['quantity']}";
                            })
                            ->minItems(1)
                            ->maxItems(20)
                            ->defaultItems(1)
                            ->addActionLabel('➕ Agregar Producto')
                            ->reorderable(false), // Desactivar reordenamiento para simplicidad
                    ]),

                // ============ PASO 4: RESUMEN Y TOTALES (CORRECCIÓN IGV) ============
                Forms\Components\Section::make('💰 Resumen del Pedido')
                    ->description('Los precios ya incluyen IGV')
                    ->icon('heroicon-o-calculator')
                    ->compact() // Reducir espaciado
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'lg' => 3,
                        ])
                            ->schema([
                                // CORRECCIÓN: Los precios YA INCLUYEN IGV
                                Forms\Components\Placeholder::make('subtotal_with_igv')
                                    ->label('💰 Total con IGV')
                                    ->content(function (Forms\Get $get): string {
                                        $products = $get('products') ?? [];
                                        $totalWithIgv = 0;
                                        foreach ($products as $product) {
                                            if (isset($product['quantity'], $product['unit_price'])) {
                                                $totalWithIgv += (int) $product['quantity'] * (float) $product['unit_price'];
                                            }
                                        }
                                        return 'S/ ' . number_format($totalWithIgv, 2);
                                    }),

                                Forms\Components\Placeholder::make('subtotal_without_igv')
                                    ->label('📄 Subtotal (sin IGV)')
                                    ->content(function (Forms\Get $get): string {
                                        $products = $get('products') ?? [];
                                        $totalWithIgv = 0;
                                        foreach ($products as $product) {
                                            if (isset($product['quantity'], $product['unit_price'])) {
                                                $totalWithIgv += (int) $product['quantity'] * (float) $product['unit_price'];
                                            }
                                        }
                                        // Calcular subtotal sin IGV: Total / 1.18
                                        $subtotalWithoutIgv = $totalWithIgv / 1.18;
                                        return 'S/ ' . number_format($subtotalWithoutIgv, 2);
                                    }),

                                Forms\Components\Placeholder::make('igv_included')
                                    ->label('🧾 IGV Incluido (18%)')
                                    ->content(function (Forms\Get $get): string {
                                        $products = $get('products') ?? [];
                                        $totalWithIgv = 0;
                                        foreach ($products as $product) {
                                            if (isset($product['quantity'], $product['unit_price'])) {
                                                $totalWithIgv += (int) $product['quantity'] * (float) $product['unit_price'];
                                            }
                                        }
                                        // Calcular IGV incluido: Total / 1.18 * 0.18
                                        $igvIncluded = ($totalWithIgv / 1.18) * 0.18;
                                        return 'S/ ' . number_format($igvIncluded, 2);
                                    }),
                            ]),

                        // Nota importante sobre precios
                        Forms\Components\Placeholder::make('price_note')
                            ->label('📌 Información Importante')
                            ->content('Los precios registrados YA INCLUYEN IGV (18%). El pago se procesará en el sistema POS.')
                            ->extraAttributes(['class' => 'text-sm text-gray-600 italic']),
                    ]),

                // ============ PASO 5: INFORMACIÓN DE ENTREGA (SIN TIEMPO ESTIMADO) ============
                Forms\Components\Section::make('🚛 Información de Entrega')
                    ->description('Repartidor y notas del pedido')
                    ->icon('heroicon-o-truck')
                    ->compact() // Reducir espaciado
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'lg' => 2,
                        ])
                            ->schema([
                                // PERMITIR ELEGIR REPARTIDOR DESDE PC
                                Forms\Components\Select::make('delivery_person_id')
                                    ->label('Repartidor')
                                    ->options(
                                        \App\Models\Employee::where('position', 'Delivery')
                                            ->selectRaw("id, CONCAT(first_name, ' ', last_name) as full_name")
                                            ->pluck('full_name', 'id')
                                    )
                                    ->searchable()
                                    ->placeholder('🚲 Seleccionar repartidor')
                                    ->helperText('Puede asignarse luego si se deja vacío')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('priority')
                                    ->label('Prioridad del Delivery')
                                    ->options([
                                        'normal' => '🟢 Normal (45-60 min)',
                                        'high' => '🟡 Alta (30-45 min)',
                                        'urgent' => '🔴 Urgente (15-30 min)',
                                    ])
                                    ->default('normal')
                                    ->required()
                                    ->native(false)
                                    ->helperText('La prioridad afecta el tiempo de entrega')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('order_notes')
                            ->label('Notas del Pedido')
                            ->placeholder('Instrucciones especiales, observaciones, referencias adicionales...')
                            ->rows(2)
                            ->helperText('Información adicional para el repartidor')
                            ->columnSpanFull(),

                        // 💡 INFORMACIÓN SOBRE EL PAGO
                        Forms\Components\Placeholder::make('payment_info')
                            ->label('💳 Información de Pago')
                            ->content('El pago de este pedido se procesará a través del sistema POS una vez creada la orden. Podrá seleccionar el método de pago (efectivo, tarjeta, Yape, etc.) desde el módulo de ventas.')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-sm bg-blue-50 p-3 rounded border-l-4 border-blue-400']),
                    ]),
            ])
            ->action(function (array $data) {
                $this->createDeliveryOrder($data);
            })
            ->modalSubmitActionLabel('🚀 Crear Pedido de Delivery')
            ->modalCancelActionLabel('❌ Cancelar');
    }

    // 🚚 MÉTODO OPTIMIZADO PARA CREAR EL PEDIDO DE DELIVERY
    protected function createDeliveryOrder(array $data): void
    {
        try {
            DB::transaction(function () use ($data) {
                // ✅ VALIDACIONES MEJORADAS
                if (empty($data['products']) || count($data['products']) === 0) {
                    throw new \Exception('Debe agregar al menos un producto al pedido');
                }

                if (empty($data['delivery_address'])) {
                    throw new \Exception('La dirección de entrega es obligatoria');
                }

                // 👤 MANEJO INTELIGENTE DEL CLIENTE (BÚSQUEDA POR TELÉFONO)
                $customer = null;

                // Si se encontró un cliente existente
                if (!empty($data['customer_id'])) {
                    $customer = Customer::find($data['customer_id']);
                }

                // Si no se encontró cliente y se proporcionaron datos para crear uno nuevo
                if (!$customer && !empty($data['search_phone']) && !($data['customer_found'] ?? false)) {
                    // Crear nuevo cliente
                    $customer = Customer::create([
                        'name' => $data['new_customer_name'] ?? 'Cliente Delivery',
                        'phone' => $data['search_phone'],
                        'email' => $data['new_customer_email'] ?? null,
                        'document_type' => $data['new_customer_document_type'] ?? 'DNI',
                        'document_number' => $data['new_customer_document_number'] ?? '00000000',
                        'address' => $data['delivery_address'],
                        'tax_validated' => false,
                    ]);
                }

                if (!$customer) {
                    throw new \Exception('No se pudo validar o crear el cliente');
                }

                // 💰 CALCULAR TOTALES CORRECTAMENTE (LOS PRECIOS YA INCLUYEN IGV)
                $totalWithIgv = 0;
                $itemCount = 0;
                foreach ($data['products'] as $productData) {
                    if (isset($productData['quantity'], $productData['unit_price'])) {
                        $totalWithIgv += $productData['quantity'] * $productData['unit_price'];
                        $itemCount += $productData['quantity'];
                    }
                }

                // Calcular desglose de IGV incluido
                $subtotalWithoutIgv = round($totalWithIgv / 1.18, 2);
                $includedIgv = round(($totalWithIgv / 1.18) * 0.18, 2);

                // Obtener la caja registradora activa
                $activeCashRegister = CashRegister::getOpenRegister();

                if (!$activeCashRegister) {
                    throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
                }

                // Obtener el empleado asociado al usuario autenticado
                $employee = Employee::where('user_id', Auth::id())->first();

                if (!$employee) {
                    throw new \Exception('No se encontró un empleado asociado al usuario actual. Por favor, contacte al administrador.');
                }

                // Crear la orden con cálculos correctos
                $order = Order::create([
                    'service_type' => 'delivery',
                    'table_id' => null,
                    'customer_id' => $customer->id,
                    'employee_id' => $employee->id,
                    'cash_register_id' => $activeCashRegister->id,
                    'order_datetime' => now(),
                    'status' => Order::STATUS_OPEN,
                    'subtotal' => $subtotalWithoutIgv,
                    'tax' => $includedIgv,
                    'discount' => 0,
                    'total' => $totalWithIgv,
                    'notes' => $data['order_notes'] ?? '',
                    'billed' => false,
                ]);

                // Agregar productos a la orden
                foreach ($data['products'] as $productData) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'subtotal' => $productData['quantity'] * $productData['unit_price'],
                        'notes' => $productData['notes'] ?? '',
                        'status' => 'pending',
                    ]);
                }

                // 🚛 CREAR REGISTRO DE DELIVERY (SIN TIEMPO ESTIMADO)
                $deliveryOrder = DeliveryOrder::create([
                    'order_id' => $order->id,
                    'delivery_address' => $data['delivery_address'],
                    'delivery_references' => $data['delivery_references'] ?? '',
                    'delivery_person_id' => $data['delivery_person_id'] ?? null,
                    'status' => 'pending',
                    'priority' => $data['priority'] ?? 'normal',
                ]);

                // 🔄 RECARGAR DATOS
                $this->loadDeliveryOrders();

                // 🎉 NOTIFICACIÓN MEJORADA DE ÉXITO
                Notification::make()
                    ->title('🎉 ¡Pedido de Delivery Creado Exitosamente!')
                    ->body("
                        📦 Pedido #{$order->id} para {$customer->name}
                        📱 {$customer->phone} | {$itemCount} items
                        💰 Total: S/ " . number_format($totalWithIgv, 2) . " (IGV incluido)
                        🚛 " . ($data['priority'] === 'urgent' ? 'URGENTE' : 'Programado') . "
                    ")
                    ->success()
                    ->duration(8000)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('ver_pedido')
                            ->label('👁️ Ver Pedido')
                            ->url('/admin/ventas/delivery/' . $deliveryOrder->id . '/edit')
                            ->button(),
                        \Filament\Notifications\Actions\Action::make('procesar_pago')
                            ->label('💳 Procesar Pago')
                            ->url('/admin/pos-interface?order_id=' . $order->id)
                            ->button()
                            ->color('warning')
                            ->openUrlInNewTab(),
                    ])
                    ->send();

                // 📊 LOG DETALLADO PARA ANÁLISIS
                Log::info('✅ Pedido de delivery creado desde el mapa de mesas', [
                    'order_id' => $order->id,
                    'delivery_order_id' => $deliveryOrder->id,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_was_created' => !($data['customer_found'] ?? false),
                    'total_with_igv' => $totalWithIgv,
                    'subtotal_without_igv' => $subtotalWithoutIgv,
                    'included_igv' => $includedIgv,
                    'items_count' => $itemCount,
                    'priority' => $data['priority'] ?? 'normal',
                    'delivery_person_assigned' => !empty($data['delivery_person_id']),
                    'delivery_address' => $data['delivery_address'],
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            // 🚨 NOTIFICACIÓN DE ERROR MEJORADA
            Notification::make()
                ->title('❌ Error al Crear Pedido de Delivery')
                ->body("
                    💥 " . $e->getMessage() . "

                    🔄 Por favor revise los datos e intente nuevamente.
                    📞 Si el problema persiste, contacte al administrador.
                ")
                ->danger()
                ->duration(12000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('reintentar')
                        ->label('Reintentar')
                        ->button()
                        ->color('primary')
                        ->action(function () {
                            // Reabrir el modal
                            $this->mountAction('newDelivery');
                        }),
                ])
                ->send();

            // 📊 LOG DETALLADO DEL ERROR
            Log::error('❌ Error al crear pedido de delivery desde mapa de mesas', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
                'form_data' => $data,
                'stack_trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
