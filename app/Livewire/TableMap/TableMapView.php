<?php

namespace App\Livewire\TableMap;

use Livewire\Component;
use App\Models\Table;
use Illuminate\Support\Collection;

class TableMapView extends Component
{
    public Collection $tables;
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $searchQuery = null;

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'locationFilter' => ['except' => '']
    ];

    public function mount(): void
    {
        $this->loadTables();
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

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('number', 'like', "%{$this->searchQuery}%")
                  ->orWhere('location', 'like', "%{$this->searchQuery}%");
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

    public function updatedSearchQuery(): void
    {
        $this->loadTables();
    }

    public function resetFilters(): void
    {
        $this->reset(['statusFilter', 'locationFilter', 'searchQuery']);
        $this->loadTables();
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

    public function getTableStatus(string $status): array
    {
        $statusInfo = [
            'available' => [
                'color' => '#22c55e', // Verde
                'bg' => '#dcfce7',    // Verde claro
                'text' => 'Disponible',
                'icon' => 'M5 13l4 4L19 7'
            ],
            'occupied' => [
                'color' => '#ef4444', // Rojo
                'bg' => '#fee2e2',    // Rojo claro
                'text' => 'Ocupada',
                'icon' => 'M6 18L18 6M6 6l12 12'
            ],
            'reserved' => [
                'color' => '#f59e0b', // Amarillo
                'bg' => '#fef3c7',    // Amarillo claro
                'text' => 'Reservada',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            'maintenance' => [
                'color' => '#6b7280', // Gris
                'bg' => '#f3f4f6',    // Gris claro
                'text' => 'Mantenimiento',
                'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
            ]
        ];

        return $statusInfo[$status] ?? $statusInfo['available'];
    }

    public function render()
    {
        return view('livewire.table-map.table-map-view')
            ->layout('layouts.tableview');
    }
}
