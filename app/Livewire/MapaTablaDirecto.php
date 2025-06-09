<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Floor;
use App\Models\Table;
use App\Models\Reservation;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class MapaTablaDirecto extends Component
{
    // Filtros con tipos estrictos
    public ?string $statusFilter = null;
    public ?int $floorFilter = null;
    public ?int $capacityFilter = null;
    
    // Propiedades del mapa
    public string $viewMode = 'grid'; // 'grid' o 'layout'
    
    // Constructor
    public function mount()
    {
        // Inicialización
    }
    
    // Métodos para control de vista
    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'layout' : 'grid';
    }
    
    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }
    
    public function render()
    {
        return view('livewire.direct-table-map', [
            'tables' => $this->getTables(),
            'floors' => Floor::all(),
            'availableCount' => $this->getAvailableTablesCount(),
            'occupiedCount' => $this->getOccupiedTablesCount(),
            'reservedCount' => $this->getReservedTablesCount(),
        ]);
    }
    
    // Método para redireccionar al POS
    public function goToPos(int $tableId): mixed
    {
        return redirect()->route('pos.index', ['table_id' => $tableId, 'preserve_cart' => 'true']);
    }
    
    // Métodos de filtrado
    public function getTables()
    {
        $query = Table::query();
        
        // Aplicar filtros si están definidos
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        if ($this->floorFilter) {
            $query->where('floor_id', $this->floorFilter);
        }
        
        if ($this->capacityFilter) {
            $query->where('capacity', '>=', $this->capacityFilter);
        }
        
        // Carga ansiosa (eager loading)
        return $query->with(['floor'])->get();
    }
    
    // Reiniciar filtros
    public function resetFilters(): void
    {
        $this->statusFilter = null;
        $this->floorFilter = null;
        $this->capacityFilter = null;
    }
    
    
    // Métodos para estadísticas
    private function getAvailableTablesCount(): int
    {
        return Table::where('status', 'available')->count();
    }
    
    private function getOccupiedTablesCount(): int
    {
        return Table::where('status', 'occupied')->count();
    }
    
    private function getReservedTablesCount(): int
    {
        return Table::where('status', 'reserved')->count();
    }
}
