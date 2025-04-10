<?php

namespace App\Livewire\TableMap;

use Livewire\Component;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Support\Collection;

class EnhancedTableMap extends Component
{
    public Collection $tables;
    public ?string $statusFilter = null;
    public ?string $locationFilter = null;
    public ?string $searchQuery = null;
    public $selectedTable = null;
    public $tableDetails = null;
    public $showQrModal = false;
    public $currentQrCode = null;

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
        $table = Table::findOrFail($tableId);
        $this->currentQrCode = $table->qr_code;
        $this->showQrModal = true;
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
        $this->currentQrCode = null;
    }

    public function changeTableStatus($tableId, $newStatus): void
    {
        $table = Table::findOrFail($tableId);
        $table->status = $newStatus;
        $table->save();

        $this->loadTables();

        if ($this->selectedTable && $this->selectedTable->id === $tableId) {
            $this->selectTable($tableId);
        }
    }

    public function getStatusColor($status): string
    {
        return match ($status) {
            Table::STATUS_AVAILABLE => 'bg-green-500',
            Table::STATUS_OCCUPIED => 'bg-red-500',
            Table::STATUS_RESERVED => 'bg-yellow-500',
            Table::STATUS_MAINTENANCE => 'bg-gray-500',
            default => 'bg-gray-300',
        };
    }

    public function getStatusText($status): string
    {
        return match ($status) {
            Table::STATUS_AVAILABLE => 'Disponible',
            Table::STATUS_OCCUPIED => 'Ocupada',
            Table::STATUS_RESERVED => 'Reservada',
            Table::STATUS_MAINTENANCE => 'Mantenimiento',
            default => 'Desconocido',
        };
    }

    public function render()
    {
        $locations = Table::select('location')->distinct()->pluck('location');

        return view('livewire.table-map.enhanced-table-map', [
            'locations' => $locations,
        ]);
    }
}
