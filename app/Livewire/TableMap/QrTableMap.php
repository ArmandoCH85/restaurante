<?php

namespace App\Livewire\TableMap;

use Livewire\Component;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Support\Collection;

class QrTableMap extends Component
{
    public Collection $tables;
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $searchQuery = null;
    public $selectedTable = null;
    public $tableDetails = null;
    public $showQrModal = false;
    public $currentQrCode = null;
    public $currentTableId = null;

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

    public function selectTable($tableId): void
    {
        $this->selectedTable = Table::findOrFail($tableId);
        $this->tableDetails = $this->getTableDetails($tableId);
    }

    public function getTableDetails($tableId): array
    {
        $table = Table::findOrFail($tableId);
        $activeOrder = Order::where('table_id', $tableId)
            ->where('status', 'in_progress')
            ->latest('order_datetime')
            ->with(['orderDetails.product', 'employee', 'customer'])
            ->first();

        return [
            'table' => $table,
            'activeOrder' => $activeOrder,
        ];
    }

    public function showQrCode($tableId): void
    {
        $this->currentTableId = $tableId;
        $table = Table::findOrFail($tableId);
        $this->currentQrCode = $table->qr_code;
        $this->showQrModal = true;
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
        $this->currentQrCode = null;
        $this->currentTableId = null;
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
                'color' => '#eab308', // Amarillo
                'bg' => '#fef9c3',    // Amarillo claro
                'text' => 'Reservada',
                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'
            ],
            'maintenance' => [
                'color' => '#64748b', // Gris
                'bg' => '#f1f5f9',    // Gris claro
                'text' => 'Mantenimiento',
                'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
            ],
        ];

        return $statusInfo[$status] ?? $statusInfo['available'];
    }

    public function render()
    {
        return view('livewire.table-map.qr-table-map');
    }
}