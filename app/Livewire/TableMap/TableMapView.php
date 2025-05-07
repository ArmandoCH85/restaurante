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
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $shapeFilter = null;
    public ?string $capacityFilter = null;
    public ?string $searchQuery = null;
    public ?string $floorFilter = null;
    public bool $showTodayReservations = false;

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'locationFilter' => ['except' => ''],
        'shapeFilter' => ['except' => ''],
        'floorFilter' => ['except' => ''],
        'showTodayReservations' => ['except' => false]
    ];

    protected $listeners = ['refresh' => 'loadTables'];

    public function mount(): void
    {
        $this->loadFloors();
        $this->loadTables();
    }

    public function loadFloors(): void
    {
        $this->floors = \App\Models\Floor::where('status', 'active')->orderBy('name')->get();
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

    public function resetFilters(): void
    {
        $this->reset(['statusFilter', 'locationFilter', 'shapeFilter', 'capacityFilter', 'searchQuery', 'floorFilter', 'showTodayReservations']);
        $this->loadTables();
    }

    public function changeTableStatus($tableId, $newStatus): void
    {
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
            $this->dispatchBrowserEvent('table-status-changed', [
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
        // Obtenemos las ubicaciones únicas desde la base de datos
        $locations = Table::select('location')
            ->distinct()
            ->whereNotNull('location')
            ->pluck('location')
            ->toArray();

        return array_combine($locations, array_map('ucfirst', $locations));
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
            return $table->id && $table->number && $table->floor_id && $table->location;
        });

        // Primero, agrupar por piso
        $tablesByFloor = $validTables->groupBy('floor_id');

        // Para cada piso, agrupar por ubicación
        foreach ($tablesByFloor as $floorId => $floorTables) {
            $floor = $this->floors->firstWhere('id', $floorId);

            // Si no se encuentra el piso, usar un objeto genérico
            if (!$floor) {
                $floor = (object)['id' => null, 'name' => 'Sin piso', 'description' => null];
            }

            // Agrupar las mesas de este piso por ubicación
            $locationGroups = $floorTables->groupBy('location');

            // Usar el ID del piso como clave en lugar del objeto completo
            $floorKey = $floor->id ?? 'no_floor';
            $groupedTables[$floorKey] = [
                'floor' => $floor,
                'locations' => $locationGroups
            ];
        }

        return $groupedTables;
    }

    public function getLocationName($location)
    {
        $names = [
            'interior' => 'Interior',
            'exterior' => 'Exterior',
            'bar' => 'Bar',
            'private' => 'Área Privada',
            '' => 'Sin ubicación'
        ];

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

    public function render()
    {
        return view('livewire.table-map.table-map-view');
    }
}
