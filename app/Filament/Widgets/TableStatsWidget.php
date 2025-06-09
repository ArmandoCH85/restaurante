<?php

namespace App\Filament\Widgets;

use App\Models\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class TableStatsWidget extends BaseWidget
{
    // Polling cada 5 segundos como pidió el jefe
    protected static ?string $pollingInterval = '5s';

    // Propiedades para filtros (recibidas desde la página)
    public ?string $statusFilter = null;
    public ?int $floorFilter = null;
    public ?int $capacityFilter = null;

    // Listener para recibir filtros actualizados
    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    /**
     * Actualizar filtros cuando la página los cambie
     */
    #[On('filtersUpdated')]
    public function updateFilters($filters): void
    {
        $this->statusFilter = $filters['statusFilter'] ?? null;
        $this->floorFilter = $filters['floorFilter'] ?? null;
        $this->capacityFilter = $filters['capacityFilter'] ?? null;
    }

    /**
     * Obtener las estadísticas exactas que pidió el jefe
     */
    protected function getStats(): array
    {
        $available = Table::where('status', 'available')->count();
        $occupied = Table::where('status', 'occupied')->count();
        $reserved = Table::where('status', 'reserved')->count();
        $total = Table::count();

        return [
            Stat::make('Disponibles', $available)
                ->description('Listas para usar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Ocupadas', $occupied)
                ->description('Con clientes')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Reservadas', $reserved)
                ->description('Con reserva')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Total', $total)
                ->description('Todas las mesas')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('gray'),
        ];
    }
}
