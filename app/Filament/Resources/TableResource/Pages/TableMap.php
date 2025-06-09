<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\Floor;
use App\Models\Table;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Actions;
use Livewire\Attributes\On;

class TableMap extends Page
{
    protected static string $resource = TableResource::class;
    
    // Definir correctamente la navegación y URLs
    protected static ?string $slug = 'map';
    
    protected static string $view = 'filament.resources.table-resource.pages.table-map';
    
    protected static ?string $title = 'Mapa de Mesas';

    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    // Propiedades para manejar los filtros
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $floorFilter = null;
    public ?string $searchQuery = null;
    public bool $showTodayReservations = false;
    public bool $showDeliveryOrders = true;
    
    // Propiedades para la selección y manejo de mesas
    public ?Table $selectedTable = null;
    public $selectedDeliveryOrder = null;
    public bool $showQrModal = false;
    public ?int $qrTableId = null;
    
    // Colecciones para almacenar datos
    public Collection $tables;
    public Collection $floors;
    public Collection $deliveryOrders;
    public Collection $locations;
    
    protected $listeners = [
        'refresh' => 'loadTables',
        'table-status-updated' => 'handleTableStatusUpdate',
        'showQrCode' => 'showQrCode'
    ];

    public function mount(): void
    {
        // Inicializar colecciones
        $this->tables = collect([]);
        $this->floors = collect([]);
        $this->deliveryOrders = collect([]);
        
        // Inicializar ubicaciones disponibles
        $this->locations = collect(['interior', 'exterior', 'terraza']);
        
        // Verificar si el usuario tiene rol de Delivery
        $user = Auth::user();
        $hasDeliveryRole = $user && $user->roles->where('name', 'Delivery')->count() > 0;
        
        if ($hasDeliveryRole) {
            $this->floors = collect([]);
            $this->loadDeliveryOrders();
            $this->showDeliveryOrders = true;
            $this->tables = collect([]);
        } else {
            $this->loadFloors();
            $this->loadTables();
            $this->loadDeliveryOrders();
        }
    }
    
    public function loadFloors(): void
    {
        $this->floors = Floor::where('status', 'active')->orderBy('name')->get();
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
            $query->where('floor_id', $this->floorFilter);
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
        $this->searchQuery = null;
        $this->showTodayReservations = false;
        $this->loadTables();
    }
    
    // Métodos para manejo de mesas
    public function selectTable(Table $table): void
    {
        $this->selectedTable = $table;
        $this->selectedDeliveryOrder = null;
    }

    public function changeTableStatus($tableId, $newStatus): void
    {
        $table = Table::find($tableId);
        if ($table) {
            $oldStatus = $table->status;
            $table->status = $newStatus;
            $table->save();

            $this->dispatch('notification', [
                'message' => "Mesa {$table->number} actualizada de {$this->getStatusName($oldStatus)} a {$this->getStatusName($newStatus)}",
                'type' => 'success'
            ]);

            $this->dispatch('table-status-updated', [
                'tableId' => $tableId,
                'newStatus' => $newStatus
            ]);

            $this->loadTables();
        }
    }

    public function handleTableStatusUpdate($data)
    {
        $this->loadTables();
    }
    
    // Métodos para QR
    public function showQrCode($tableId): void
    {
        $this->qrTableId = $tableId;
        $this->showQrModal = true;
    }
    
    public function closeQrModal(): void
    {
        $this->showQrModal = false;
        $this->qrTableId = null;
    }
    
    // Métodos auxiliares
    public function getStatusName($status): string
    {
        return match ($status) {
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'Mantenimiento',
            default => $status,
        };
    }
    
    public function getStatusColor($status): string
    {
        return match ($status) {
            'available' => 'bg-green-500',
            'occupied' => 'bg-red-500',
            'reserved' => 'bg-yellow-500',
            'maintenance' => 'bg-gray-500',
            default => 'bg-gray-400',
        };
    }
    
    public function getStatusTextColor($status): string
    {
        return match ($status) {
            'available' => 'bg-green-100 text-green-800',
            'occupied' => 'bg-red-100 text-red-800',
            'reserved' => 'bg-yellow-100 text-yellow-800',
            'maintenance' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    public function getLocationName($location): string
    {
        return match ($location) {
            'interior' => 'Interior',
            'exterior' => 'Exterior',
            'terraza' => 'Terraza',
            default => $location,
        };
    }
    
    public function getFloorName($floorId): string
    {
        $floor = $this->floors->firstWhere('id', $floorId);
        return $floor ? $floor->name : '';
    }
    
    public function getDeliveryStatusName(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => $status,
        };
    }
    
    public function getDeliveryStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-blue-500',
            'assigned' => 'bg-yellow-500',
            'in_transit' => 'bg-indigo-500',
            'delivered' => 'bg-green-500',
            'cancelled' => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label('Actualizar')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadTables();
                    $this->loadDeliveryOrders();
                    $this->dispatch('notification', [
                        'message' => 'Mapa de mesas actualizado',
                        'type' => 'success'
                    ]);
                }),
        ];
    }
}
