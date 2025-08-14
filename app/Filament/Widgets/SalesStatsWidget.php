<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use Carbon\Carbon;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;

class SalesStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?int $sort = 1;

    // 📐 GRID RESPONSIVO - 3 ESTADÍSTICAS PRINCIPALES
    protected int | string | array $columnSpan = [
        'default' => 'full',  // Móvil: ancho completo
        'sm' => 'full',       // Tablet pequeña: ancho completo
        'md' => 'full',       // Tablet: ancho completo (3 estadísticas en línea)
        'lg' => 'full',       // Desktop: ancho completo
        'xl' => 'full',       // Desktop grande: ancho completo
        '2xl' => 'full',      // Desktop extra: ancho completo
    ];

    // 🔄 PROPIEDADES PARA REACTIVIDAD
    protected static bool $isLazy = false;

    // 📊 LISTENERS PARA ACTUALIZACIÓN AUTOMÁTICA
    protected $listeners = [
        'filtersFormUpdated' => '$refresh',
        'updateCharts' => '$refresh',
    ];

    // 🎯 MÉTODO PARA FORZAR ACTUALIZACIÓN CUANDO CAMBIAN LOS FILTROS
    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
    }

    protected function getStats(): array
    {
        [$start, $end] = $this->resolveDateRange($this->filters ?? []);

        return [
            $this->getTotalSalesStat($start, $end),
            $this->getOperationsCountStat($start, $end),
            $this->getMesaSalesStat($start, $end),
        ];
    }

    // 🔢 N° OPERACIONES
    private function getOperationsCountStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $count = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->where('billed', true)
            ->count();

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Órdenes Facturadas', number_format($count))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-calculator')
            ->color('primary');
    }

    // 💰 TOTAL VENTAS
    private function getTotalSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->where('billed', true)
            ->sum('total');

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Ventas Facturadas', 'S/ ' . number_format($total, 2))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success');
    }

    // 🍽️ VENTAS EN MESA
    private function getMesaSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('service_type', 'dine_in')
            ->where('status', '!=', 'cancelled')
            ->where('billed', true)
            ->sum('total');

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Ventas en Mesa', 'S/ ' . number_format($total, 2))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-home')
            ->color('emerald');
    }

    // Wrapper para compatibilidad con código previo
    private function getDateRangeDescription(Carbon $startDate, Carbon $endDate): string
    {
        return $this->humanRangeLabel($startDate, $endDate);
    }

    /**
     * 📈 Gráfico simple de ventas de los últimos 7 días
     */
    // Eliminado gráfico embebido ficticio para evitar datos sinteticos
    private function getSalesChart(): array { return []; }
}
