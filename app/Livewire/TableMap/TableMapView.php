<?php

namespace App\Livewire\TableMap;

use Livewire\Component;
use App\Models\Table;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class TableMapView extends Component
{
    public Collection $tables;
    public Collection $floors;
    public Collection $deliveryOrders;
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $shapeFilter = null;
    public ?string $capacityFilter = null;
    public ?string $searchQuery = null;
    public ?string $floorFilter = null;
    public bool $showTodayReservations = false;
    public bool $showDeliveryOrders = true; // Siempre visible por defecto

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'locationFilter' => ['except' => ''],
        'shapeFilter' => ['except' => ''],
        'floorFilter' => ['except' => ''],
        'showTodayReservations' => ['except' => false],
        'preserve_cart' => ['except' => '']
    ];

    public ?string $preserve_cart = null;

    protected $listeners = ['refresh' => 'loadTables'];

    public function mount(): void
    {
        // Verificar si el usuario tiene acceso a este componente
        $user = \Illuminate\Support\Facades\Auth::user();
        $hasDeliveryRole = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        // Registrar información para depuración
        \Illuminate\Support\Facades\Log::info('TableMapView::mount', [
            'user_id' => $user ? $user->id : 'no_user',
            'name' => $user ? $user->name : 'no_name',
            'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No'
        ]);

        // Si el usuario tiene rol Delivery, solo cargar pedidos de delivery
        if ($hasDeliveryRole) {
            $this->floors = collect([]);
            $this->loadDeliveryOrders();
            $this->showDeliveryOrders = true;

            // Ocultar las mesas físicas para usuarios con rol Delivery
            $this->tables = collect([]);
        } else {
            // Para otros usuarios, cargar todo normalmente
            $this->loadFloors();
            $this->loadTables();
            $this->loadDeliveryOrders();

            // Registrar información de depuración
            \Illuminate\Support\Facades\Log::info('TableMapView montado', [
                'showDeliveryOrders' => $this->showDeliveryOrders,
                'deliveryOrdersCount' => $this->deliveryOrders->count()
            ]);
        }
    }

    public function loadFloors(): void
    {
        $this->floors = \App\Models\Floor::where('status', 'active')->orderBy('name')->get();
    }

    public function loadTables(): void
    {
        // Cargar mesas físicas
        $query = Table::query();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->locationFilter) {
            $query->where('location', $this->locationFilter);
        }

        if ($this->shapeFilter) {
            $query->where('shape', $this->shapeFilter);
        }

        if ($this->floorFilter) {
            $query->where('floor_id', $this->floorFilter);
        }

        if ($this->capacityFilter) {
            switch ($this->capacityFilter) {
                case '1-2':
                    $query->whereBetween('capacity', [1, 2]);
                    break;
                case '3-4':
                    $query->whereBetween('capacity', [3, 4]);
                    break;
                case '5-8':
                    $query->whereBetween('capacity', [5, 8]);
                    break;
                case '9+':
                    $query->where('capacity', '>=', 9);
                    break;
            }
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('number', 'like', "%{$this->searchQuery}%")
                  ->orWhere('location', 'like', "%{$this->searchQuery}%");
            });
        }

        // Cargar las mesas con sus reservas activas
        $query->with(['activeReservations' => function($query) {
            $query->whereDate('reservation_date', Carbon::today());
        }]);

        // Filtrar por mesas con reservas para hoy si está activado
        if ($this->showTodayReservations) {
            $query->whereHas('activeReservations', function($query) {
                $query->whereDate('reservation_date', Carbon::today());
            });
        }

        $this->tables = $query->orderBy('location')->orderBy('number')->get();

        // Cargar pedidos de delivery activos como "mesas virtuales"
        if ($this->showDeliveryOrders) {
            $this->loadDeliveryOrders();
        } else {
            $this->deliveryOrders = collect([]);
        }
    }

    /**
     * Carga los pedidos de delivery activos para mostrarlos como "mesas virtuales"
     */
    public function loadDeliveryOrders(): void
    {
        // Cargar pedidos de delivery activos (pendientes, asignados o en tránsito)
        $query = \App\Models\DeliveryOrder::with(['order.customer', 'deliveryPerson'])
            ->whereIn('status', ['pending', 'assigned', 'in_transit']);

        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        // Si el usuario es un repartidor, solo mostrar sus pedidos asignados
        if ($isDeliveryPerson) {
            // Obtener el empleado asociado al usuario actual
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();

            if ($employee) {
                $query->where('delivery_person_id', $employee->id);
            }
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('delivery_address', 'like', "%{$this->searchQuery}%")
                  ->orWhereHas('order.customer', function($sq) {
                      $sq->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('phone', 'like', "%{$this->searchQuery}%");
                  });
            });
        }

        $this->deliveryOrders = $query->orderBy('created_at', 'desc')->get();

        // Registrar el resultado para depuración
        \Illuminate\Support\Facades\Log::info('Pedidos de delivery cargados', [
            'count' => $this->deliveryOrders->count(),
            'ids' => $this->deliveryOrders->pluck('id')->toArray()
        ]);

        // Verificar si hay pedidos de delivery sin cliente asociado
        foreach ($this->deliveryOrders as $delivery) {
            if (!$delivery->order) {
                \Illuminate\Support\Facades\Log::warning('Pedido de delivery sin orden asociada', [
                    'delivery_id' => $delivery->id,
                    'order_id' => $delivery->order_id
                ]);
            } elseif (!$delivery->order->customer) {
                \Illuminate\Support\Facades\Log::warning('Pedido de delivery sin cliente asociado', [
                    'delivery_id' => $delivery->id,
                    'order_id' => $delivery->order_id
                ]);
            }
        }
    }

    public function updatedStatusFilter(): void
    {
        $this->loadTables();
    }

    public function updatedLocationFilter(): void
    {
        $this->loadTables();
    }

    public function updatedShapeFilter(): void
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
    }

    public function updatedShowTodayReservations(): void
    {
        $this->loadTables();
    }

    public function updatedShowDeliveryOrders(): void
    {
        $this->loadTables();
    }

    public function resetFilters(): void
    {
        $this->reset(['statusFilter', 'locationFilter', 'shapeFilter', 'capacityFilter', 'searchQuery', 'floorFilter', 'showTodayReservations']);
        $this->loadTables();
    }

    /**
     * Actualiza el estado de un pedido de delivery
     */
    public function updateDeliveryStatus($deliveryOrderId, string $newStatus): void
    {
        // Asegurarse de que $deliveryOrderId sea un entero
        if (is_array($deliveryOrderId)) {
            // Si es un array, tomamos el primer elemento
            $deliveryOrderId = $deliveryOrderId[0] ?? null;
        }

        // Convertir a entero
        $deliveryOrderId = (int) $deliveryOrderId;

        $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrderId);
        if (!$deliveryOrder) {
            $this->dispatch('notification', [
                'message' => "Pedido no encontrado",
                'type' => 'error'
            ]);
            return;
        }

        // Verificar si el usuario tiene permiso para actualizar este pedido
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
        $employee = $isDeliveryPerson ? \App\Models\Employee::where('user_id', $user->id)->first() : null;

        // Para estados "in_transit" y "delivered", solo el repartidor asignado puede actualizar
        if (in_array($newStatus, ['in_transit', 'delivered'])) {
            if (!$isDeliveryPerson) {
                $this->dispatch('notification', [
                    'message' => "Solo los repartidores pueden actualizar el estado de entrega",
                    'type' => 'error'
                ]);
                return;
            }

            if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                $this->dispatch('notification', [
                    'message' => "Solo el repartidor asignado puede actualizar este pedido",
                    'type' => 'error'
                ]);
                return;
            }
        }

        $previousStatus = $deliveryOrder->status;
        $success = false;

        switch ($newStatus) {
            case 'assigned':
                // Mostrar modal para asignar repartidor
                $this->dispatch('openAssignDeliveryPersonModal', $deliveryOrderId);
                return;

            case 'in_transit':
                // Solo el repartidor asignado puede marcar como en tránsito
                $success = $deliveryOrder->markAsInTransit();
                break;

            case 'delivered':
                // Solo el repartidor asignado puede marcar como entregado
                $success = $deliveryOrder->markAsDelivered();
                break;

            case 'cancelled':
                // Mostrar modal para ingresar motivo de cancelación
                $this->dispatch('openCancelDeliveryModal', $deliveryOrderId);
                return;
        }

        if ($success) {
            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->loadDeliveryOrders();
            $this->dispatch('notification', [
                'message' => "Estado del pedido actualizado a " . $this->getDeliveryStatusName($newStatus),
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notification', [
                'message' => "No se pudo actualizar el estado del pedido",
                'type' => 'error'
            ]);
        }
    }

    /**
     * Asigna un repartidor a un pedido de delivery
     */
    public function assignDeliveryPerson($deliveryOrderId, $employeeId): void
    {
        // Asegurarse de que $deliveryOrderId sea un entero
        if (is_array($deliveryOrderId)) {
            // Si es un array, tomamos el primer elemento
            $deliveryOrderId = $deliveryOrderId[0] ?? null;
        }

        // Convertir a entero
        $deliveryOrderId = (int) $deliveryOrderId;
        $employeeId = (int) $employeeId;

        $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrderId);
        $employee = \App\Models\Employee::find($employeeId);

        if (!$deliveryOrder || !$employee) {
            $this->dispatch('notification', [
                'message' => "Pedido o repartidor no encontrado",
                'type' => 'error'
            ]);
            return;
        }

        $previousStatus = $deliveryOrder->status;
        $success = $deliveryOrder->assignDeliveryPerson($employee);

        if ($success) {
            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->loadDeliveryOrders();
            $this->dispatch('notification', [
                'message' => "Repartidor {$employee->full_name} asignado al pedido #{$deliveryOrder->order_id}",
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notification', [
                'message' => "No se pudo asignar el repartidor al pedido",
                'type' => 'error'
            ]);
        }
    }

    /**
     * Cancela un pedido de delivery
     */
    public function cancelDelivery($deliveryOrderId, ?string $reason = null): void
    {
        // Asegurarse de que $deliveryOrderId sea un entero
        if (is_array($deliveryOrderId)) {
            // Si es un array, tomamos el primer elemento
            $deliveryOrderId = $deliveryOrderId[0] ?? null;
        }

        // Convertir a entero
        $deliveryOrderId = (int) $deliveryOrderId;

        $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrderId);

        if (!$deliveryOrder) {
            $this->dispatch('notification', [
                'message' => "Pedido no encontrado",
                'type' => 'error'
            ]);
            return;
        }

        // Verificar si el usuario tiene permiso para cancelar este pedido
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
        $employee = $isDeliveryPerson ? \App\Models\Employee::where('user_id', $user->id)->first() : null;

        // Si es un repartidor, solo puede cancelar sus propios pedidos
        if ($isDeliveryPerson) {
            if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                $this->dispatch('notification', [
                    'message' => "Solo puedes cancelar tus propios pedidos asignados",
                    'type' => 'error'
                ]);
                return;
            }
        }

        $previousStatus = $deliveryOrder->status;
        $success = $deliveryOrder->cancel($reason);

        if ($success) {
            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->loadDeliveryOrders();
            $this->dispatch('notification', [
                'message' => "Pedido #{$deliveryOrder->order_id} cancelado",
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notification', [
                'message' => "No se pudo cancelar el pedido",
                'type' => 'error'
            ]);
        }
    }

    /**
     * Obtiene el nombre legible de un estado de delivery
     */
    public function getDeliveryStatusName(string $status): string
    {
        $statusNames = [
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        return $statusNames[$status] ?? $status;
    }

    /**
     * Obtiene el conteo de pedidos asignados para el usuario actual
     */
    public function getAssignedCount(): int
    {
        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        if (!$isDeliveryPerson) {
            return $this->deliveryOrders->where('status', 'assigned')->count();
        }

        // Obtener el empleado asociado al usuario actual
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return 0;
        }

        // Filtrar por el repartidor actual
        return $this->deliveryOrders->where('status', 'assigned')
            ->where('delivery_person_id', $employee->id)
            ->count();
    }

    /**
     * Obtiene el conteo de pedidos en tránsito para el usuario actual
     */
    public function getInTransitCount(): int
    {
        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        if (!$isDeliveryPerson) {
            return $this->deliveryOrders->where('status', 'in_transit')->count();
        }

        // Obtener el empleado asociado al usuario actual
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return 0;
        }

        // Filtrar por el repartidor actual
        return $this->deliveryOrders->where('status', 'in_transit')
            ->where('delivery_person_id', $employee->id)
            ->count();
    }

    /**
     * Obtiene el conteo de pedidos entregados (últimas 24 horas) para el usuario actual
     */
    public function getDeliveredCount(): int
    {
        // Obtener pedidos entregados en las últimas 24 horas
        $yesterday = \Carbon\Carbon::now()->subDay();

        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        if (!$isDeliveryPerson) {
            return \App\Models\DeliveryOrder::where('status', 'delivered')
                ->where('actual_delivery_time', '>=', $yesterday)
                ->count();
        }

        // Obtener el empleado asociado al usuario actual
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            return 0;
        }

        // Filtrar por el repartidor actual
        return \App\Models\DeliveryOrder::where('status', 'delivered')
            ->where('delivery_person_id', $employee->id)
            ->where('actual_delivery_time', '>=', $yesterday)
            ->count();
    }

    /**
     * Abre el modal para cancelar un pedido
     */
    public function openCancelModal($deliveryId): void
    {
        $this->dispatch('openCancelDeliveryModal', $deliveryId);
    }

    /**
     * Obtiene la información de estilo para un estado de delivery
     */
    public function getDeliveryStatusInfo(string $status): array
    {
        $statusInfo = [
            'pending' => [
                'color' => '#92400e', // Naranja oscuro
                'bg' => '#fef3c7',    // Amarillo claro
                'text' => 'Pendiente',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            'assigned' => [
                'color' => '#1e40af', // Azul oscuro
                'bg' => '#dbeafe',    // Azul claro
                'text' => 'Asignado',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
            ],
            'in_transit' => [
                'color' => '#4338ca', // Índigo oscuro
                'bg' => '#e0e7ff',    // Índigo claro
                'text' => 'En tránsito',
                'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'
            ],
            'delivered' => [
                'color' => '#065f46', // Verde oscuro
                'bg' => '#d1fae5',    // Verde claro
                'text' => 'Entregado',
                'icon' => 'M5 13l4 4L19 7'
            ],
            'cancelled' => [
                'color' => '#991b1b', // Rojo oscuro
                'bg' => '#fee2e2',    // Rojo claro
                'text' => 'Cancelado',
                'icon' => 'M6 18L18 6M6 6l12 12'
            ]
        ];

        return $statusInfo[$status] ?? $statusInfo['pending'];
    }

    public function changeTableStatus($tableId, $newStatus): void
    {
        // Asegurarse de que $tableId sea un entero
        if (is_array($tableId)) {
            // Si es un array, tomamos el primer elemento
            $tableId = $tableId[0] ?? null;
        }

        // Convertir a entero
        $tableId = (int) $tableId;

        $table = Table::find($tableId);
        if ($table) {
            // Si la mesa se está ocupando, registramos el tiempo de inicio
            if ($newStatus === 'occupied') {
                $table->occupied_at = now();
            } else if ($table->status === 'occupied' && $newStatus !== 'occupied') {
                // Si la mesa estaba ocupada y ahora cambia a otro estado, limpiamos el tiempo
                $table->occupied_at = null;
            }

            $table->status = $newStatus;
            $table->save();
            $this->loadTables();
            $this->dispatch('table-status-changed', [
                'message' => "Mesa {$table->number} ahora está " . $this->getStatusName($newStatus),
                'type' => 'success'
            ]);
        }
    }

    public function getStatusName($status): string
    {
        $statusNames = [
            'available' => 'disponible',
            'occupied' => 'ocupada',
            'reserved' => 'reservada',
            'maintenance' => 'en mantenimiento'
        ];

        return $statusNames[$status] ?? $status;
    }

    public function getStatusOptions(): array
    {
        return [
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'En mantenimiento',
        ];
    }

    public function getLocationOptions(): array
    {
        // Valores predeterminados para las ubicaciones
        $defaultLocations = [
            'interior' => 'Interior',
            'exterior' => 'Exterior',
            'bar' => 'Bar',
            'private' => 'Área Privada',
            'default' => 'Sin ubicación'
        ];

        // Intentamos obtener las ubicaciones únicas desde la base de datos
        try {
            $locations = Table::select('location')
                ->distinct()
                ->whereNotNull('location')
                ->pluck('location')
                ->toArray();

            // Si hay ubicaciones en la base de datos, las usamos
            if (!empty($locations)) {
                $dbLocations = array_combine($locations, array_map('ucfirst', $locations));
                return array_merge($defaultLocations, $dbLocations);
            }
        } catch (\Exception) {
            // Si hay un error, usamos solo los valores predeterminados
        }

        return $defaultLocations;
    }

    public function getFloorOptions(): array
    {
        // Convertir la colección de pisos a un array de opciones para el selector
        $options = [];
        foreach ($this->floors as $floor) {
            $options[$floor->id] = $floor->name;
        }
        return $options;
    }

    public function getTableStatus(string $status): array
    {
        $statusInfo = [
            'available' => [
                'color' => '#166534', // Verde oscuro para mejor contraste
                'bg' => '#dcfce7',    // Verde claro
                'text' => 'Disponible',
                'icon' => 'M5 13l4 4L19 7'
            ],
            'occupied' => [
                'color' => '#991b1b', // Rojo oscuro para mejor contraste
                'bg' => '#fee2e2',    // Rojo claro
                'text' => 'Ocupada',
                'icon' => 'M6 18L18 6M6 6l12 12'
            ],
            'reserved' => [
                'color' => '#9a3412', // Naranja oscuro para mejor contraste
                'bg' => '#fef3c7',    // Amarillo claro
                'text' => 'Reservada',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            'maintenance' => [
                'color' => '#374151', // Gris oscuro para mejor contraste
                'bg' => '#f3f4f6',    // Gris claro
                'text' => 'Mantenimiento',
                'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
            ]
        ];

        return $statusInfo[$status] ?? $statusInfo['available'];
    }

    public function getShapeOptions(): array
    {
        return [
            'square' => 'Cuadrada',
            'round' => 'Redonda',
        ];
    }

    public function getGroupedTables()
    {
        // Agrupar las mesas por piso y luego por ubicación
        $groupedTables = [];

        // Filtrar mesas inválidas
        $validTables = $this->tables->filter(function($table) {
            return $table->id && $table->number;
        });

        // Asegurar que floor_id y location tengan valores predeterminados
        $validTables = $validTables->map(function($table) {
            if (!$table->floor_id) {
                $table->floor_id = 0; // Valor predeterminado para pisos no asignados
            }
            if (!$table->location) {
                $table->location = 'default'; // Valor predeterminado para ubicaciones no asignadas
            }
            return $table;
        });

        // Primero, agrupar por piso
        $tablesByFloor = $validTables->groupBy('floor_id');

        // Para cada piso, agrupar por ubicación
        foreach ($tablesByFloor as $floorId => $floorTables) {
            // Agrupar las mesas de este piso por ubicación
            $locationGroups = $floorTables->groupBy('location');

            // Agregar directamente al array de resultados
            $groupedTables[$floorId] = $locationGroups;
        }

        return $groupedTables;
    }

    public function getLocationName($location)
    {
        $names = $this->getLocationOptions();
        return $names[$location] ?? ucfirst($location);
    }

    public function getTableStats()
    {
        $total = $this->tables->count();
        $available = $this->tables->where('status', 'available')->count();
        $occupied = $this->tables->where('status', 'occupied')->count();
        $reserved = $this->tables->where('status', 'reserved')->count();
        $maintenance = $this->tables->where('status', 'maintenance')->count();

        return [
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'reserved' => $reserved,
            'maintenance' => $maintenance,
            'available_percent' => $total > 0 ? round(($available / $total) * 100) : 0,
            'occupied_percent' => $total > 0 ? round(($occupied / $total) * 100) : 0
        ];
    }

    public function getOccupationTime($table)
    {
        if ($table->status !== 'occupied' || !$table->occupied_at) {
            return null;
        }

        $occupiedAt = new \DateTime($table->occupied_at);
        $now = new \DateTime();
        $diff = $occupiedAt->diff($now);

        if ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        } else {
            return $diff->i . ' minutos';
        }
    }

    public function getOccupationTimeClass($table)
    {
        if ($table->status !== 'occupied' || !$table->occupied_at) {
            return '';
        }

        $occupiedAt = new \DateTime($table->occupied_at);
        $now = new \DateTime();
        $diffMinutes = ($now->getTimestamp() - $occupiedAt->getTimestamp()) / 60;

        if ($diffMinutes < 30) {
            return 'occupation-time-short';
        } elseif ($diffMinutes < 90) {
            return 'occupation-time-medium';
        } else {
            return 'occupation-time-long';
        }
    }

    public function getFloorName($floorId)
    {
        if ($floorId == 0) {
            return 'Sin piso asignado';
        }

        $floor = $this->floors->firstWhere('id', $floorId);
        return $floor ? $floor->name : 'Piso desconocido';
    }

    public function render()
    {
        // Verificar si el usuario tiene rol Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $hasDeliveryRole = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        if ($hasDeliveryRole) {
            // Para usuarios con rol Delivery, mostrar solo la sección de pedidos de delivery
            return view('livewire.table-map.delivery-only-view');
        } else {
            // Para otros usuarios, mostrar la vista completa
            return view('livewire.table-map.table-map-view-new');
        }
    }
}
