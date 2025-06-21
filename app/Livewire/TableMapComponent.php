<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Table;
use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class TableMapComponent extends Component
{
    // Filtros con tipos estrictos
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $floorFilter = null;
    public ?string $searchQuery = null;
    public bool $showTodayReservations = false;
    public string $viewMode = 'grid'; // 'grid' o 'layout'

    // Propiedades para la selección y manejo de mesas
    public ?Table $selectedTable = null;
    public ?int $selectedTableId = null;

    // Propiedades para modales
    public bool $showChangeStatusModal = false;
    public bool $showAssignOrderModal = false;
    public bool $showReservationInfoModal = false;
    public ?string $newStatus = null;
    public ?int $current_diners = null;

    // Propiedades para operaciones
    public ?int $orderIdToAssign = null;
    public ?array $tablePositions = [];
    public bool $isEditingLayout = false;

    protected function rules()
    {
        return [
            'current_diners' => [
                'required_if:newStatus,occupied',
                'integer',
                'min:1',
                'max:' . ($this->selectedTable?->capacity ?? 1)
            ],
        ];
    }

    protected function messages()
    {
        return [
            'current_diners.required_if' => 'El número de comensales es requerido cuando se ocupa una mesa.',
            'current_diners.integer' => 'El número de comensales debe ser un número entero.',
            'current_diners.min' => 'El número de comensales debe ser al menos 1.',
            'current_diners.max' => 'El número de comensales no puede exceder la capacidad de la mesa (:max).',
        ];
    }

    public function mount(): void
    {
        // Inicialización del componente
        $this->loadTablePositions();
    }

    /**
     * Carga las posiciones guardadas de las mesas para el modo layout
     */
    protected function loadTablePositions(): void
    {
        // Cargar posiciones guardadas de las mesas (se podría implementar con un modelo Layout)
        // Por ahora simplemente asignaremos posiciones por defecto para pruebas
        $tables = $this->loadTables();
        $this->tablePositions = [];

        // Asignar posiciones estáticas iniciales por categoría en un grid
        $interiorX = 50;
        $interiorY = 50;
        $exteriorX = 300;
        $exteriorY = 50;
        $terrazaX = 550;
        $terrazaY = 50;

        foreach ($tables as $table) {
            $x = 50;
            $y = 50;

            switch ($table->location) {
                case 'interior':
                    $x = $interiorX;
                    $y = $interiorY;
                    $interiorY += 100;
                    break;
                case 'exterior':
                    $x = $exteriorX;
                    $y = $exteriorY;
                    $exteriorY += 100;
                    break;
                case 'terraza':
                    $x = $terrazaX;
                    $y = $terrazaY;
                    $terrazaY += 100;
                    break;
            }

            $this->tablePositions[$table->id] = [
                'x' => $x,
                'y' => $y,
                'rotation' => 0
            ];
        }
    }

    /**
     * Carga las mesas con filtros aplicados
     */
    public function loadTables(): Collection
    {
        $query = Table::query();

        // Aplicar filtros si están definidos
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

        // Cargar relaciones necesarias
        $query->with([
            'activeReservations' => function ($query) {
                $query->whereDate('reservation_date', Carbon::today());
            },
            'activeOrders'
        ]);

        // Mostrar solo mesas con reservas para hoy si el filtro está activo
        if ($this->showTodayReservations) {
            $query->whereHas('activeReservations', function ($query) {
                $query->whereDate('reservation_date', Carbon::today());
            });
        }

        return $query->orderBy('location')->orderBy('number')->get();
    }

    /**
     * Selecciona una mesa para mostrar sus detalles
     * @param int $tableId ID de la mesa seleccionada
     * @param bool $redirect Si es verdadero, redirecciona al POS
     */
    public function selectTable(int $tableId, bool $redirect = false): mixed
    {
        $this->selectedTableId = $tableId;
        $this->selectedTable = Table::with(['activeReservations', 'activeOrders'])->find($tableId);

        // Si se solicita redirección, ir directamente al POS
        if ($redirect) {
            return redirect()->route('pos.index', ['table_id' => $tableId, 'preserve_cart' => 'true']);
        }

        return null;
    }

    /**
     * Redirecciona directamente al POS para una mesa específica
     * @param int $tableId ID de la mesa a la que se quiere ir en el POS
     * @return mixed Objeto de redirección
     */
    public function goToPos(int $tableId): mixed
    {
        return redirect()->route('pos.index', ['table_id' => $tableId, 'preserve_cart' => 'true']);
    }

    /**
     * Deselecciona la mesa actual
     */
    public function unselectTable(): void
    {
        $this->selectedTableId = null;
        $this->selectedTable = null;
        $this->resetModals();
    }

    /**
     * Limpia todos los filtros aplicados
     */
    public function clearFilters(): void
    {
        $this->statusFilter = null;
        $this->locationFilter = null;
        $this->floorFilter = null;
        $this->searchQuery = null;
    }

    /**
     * Abre el modal para cambiar el estado de una mesa
     */
    public function openChangeStatusModal(string $status): void
    {
        if (!$this->selectedTable) return;

        $this->newStatus = $status;
        $this->current_diners = null;
        $this->showChangeStatusModal = true;
    }

    /**
     * Cambia el estado de la mesa seleccionada
     */
    public function changeTableStatus(): void
    {
        if (!$this->selectedTable || !$this->newStatus) return;

        // Validar si se está ocupando la mesa
        if ($this->newStatus === 'occupied') {
            $this->validate();
        }

        $this->selectedTable->status = $this->newStatus;

        // Actualizar el número de comensales y la hora de ocupación
        if ($this->newStatus === 'occupied') {
            $this->selectedTable->current_diners = $this->current_diners;
            $this->selectedTable->occupied_at = now();
        } else {
            $this->selectedTable->current_diners = null;
            $this->selectedTable->occupied_at = null;
        }

        $this->selectedTable->save();

        $this->showChangeStatusModal = false;
        $this->newStatus = null;
        $this->current_diners = null;

        // Notificar al usuario
        Notification::make()
            ->title('Estado actualizado')
            ->body("La mesa {$this->selectedTable->number} ahora está {$this->getStatusLabel($this->selectedTable->status)}.")
            ->success()
            ->send();

        // Recargar la mesa seleccionada
        $this->selectTable($this->selectedTable->id);
    }

    /**
     * Devuelve la etiqueta legible para un estado
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'disponible',
            'occupied' => 'ocupada',
            'reserved' => 'reservada',
            'maintenance' => 'en mantenimiento',
            default => $status,
        };
    }

    /**
     * Alterna entre los modos de visualización
     */
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'layout' : 'grid';
    }

    /**
     * Activa/desactiva el modo de edición de layout
     */
    public function toggleEditLayout(): void
    {
        $this->isEditingLayout = !$this->isEditingLayout;
    }

    /**
     * Guarda la posición de una mesa en el modo layout
     */
    public function saveTablePosition(int $tableId, float $x, float $y, float $rotation = 0): void
    {
        $this->tablePositions[$tableId] = [
            'x' => $x,
            'y' => $y,
            'rotation' => $rotation
        ];

        // Aquí se podría guardar en la base de datos si se implementa un modelo de Layout
    }

    /**
     * Resetea todas las posiciones de las mesas a su valor por defecto
     */
    public function resetLayout(): void
    {
        $this->loadTablePositions();

        Notification::make()
            ->title('Layout reiniciado')
            ->body("Se han reiniciado las posiciones de todas las mesas.")
            ->info()
            ->send();
    }

    /**
     * Obtiene los pisos disponibles
     */
    public function getFloors(): Collection
    {
        return Floor::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtiene los posibles estados de las mesas
     */
    public function getStatusOptions(): array
    {
        return [
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'Mantenimiento',
        ];
    }

    /**
     * Obtiene las posibles ubicaciones de las mesas
     */
    public function getLocationOptions(): array
    {
        return [
            'interior' => 'Interior',
            'exterior' => 'Exterior',
            'terraza' => 'Terraza',
        ];
    }

    /**
     * Métodos que se activan cuando cambian los filtros
     */
    public function updatedStatusFilter(): void
    {
        // Se usará el refresco automático por LiveWire
    }

    public function updatedLocationFilter(): void
    {
        // Se usará el refresco automático por LiveWire
    }

    public function updatedFloorFilter(): void
    {
        // Se usará el refresco automático por LiveWire
    }

    public function updatedSearchQuery(): void
    {
        // Se usará el refresco automático por LiveWire
    }

    /**
     * Reinicia todos los filtros a su valor por defecto
     */
    public function resetFilters(): void
    {
        $this->statusFilter = null;
        $this->locationFilter = null;
        $this->floorFilter = null;
        $this->searchQuery = null;
        $this->showTodayReservations = false;
    }

    /**
     * Reinicia todos los modales a su estado cerrado
     */
    protected function resetModals(): void
    {
        $this->showChangeStatusModal = false;
        $this->showAssignOrderModal = false;
        $this->showReservationInfoModal = false;
        $this->newStatus = null;
        $this->orderIdToAssign = null;
    }

    /**
     * Obtiene las órdenes pendientes para asignar a mesas
     */
    #[Computed]
    public function getPendingOrders(): Collection
    {
        return Order::where('status', 'pending')
            ->orWhere('status', 'processing')
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Renderiza el componente
     */
    public function render()
    {
        return view('livewire.table-map-component', [
            'tables' => $this->loadTables(),
            'floors' => $this->getFloors(),
            'statusOptions' => $this->getStatusOptions(),
            'locationOptions' => $this->getLocationOptions(),
            'pendingOrders' => $this->getPendingOrders, // Property computada
        ]);
    }
}
