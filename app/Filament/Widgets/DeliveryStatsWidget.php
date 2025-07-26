<?php

namespace App\Filament\Widgets;

use App\Models\DeliveryOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DeliveryStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected function getStats(): array
    {
        // Obtener estadísticas en tiempo real
        $stats = DeliveryOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Estadísticas de hoy
        $todayStats = DeliveryOrder::whereDate('created_at', today())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Calcular totales
        $pendingCount = $stats['pending'] ?? 0;
        $assignedCount = $stats['assigned'] ?? 0;
        $inTransitCount = $stats['in_transit'] ?? 0;
        $deliveredCount = $stats['delivered'] ?? 0;
        $cancelledCount = $stats['cancelled'] ?? 0;
        
        $deliveredToday = $todayStats['delivered'] ?? 0;
        $totalToday = array_sum($todayStats);

        // Calcular tendencias (comparar con ayer)
        $yesterdayDelivered = DeliveryOrder::whereDate('created_at', yesterday())
            ->where('status', 'delivered')
            ->count();
            
        $deliveryTrend = $yesterdayDelivered > 0 
            ? (($deliveredToday - $yesterdayDelivered) / $yesterdayDelivered) * 100 
            : ($deliveredToday > 0 ? 100 : 0);

        return [
            Stat::make('Pedidos Pendientes', $pendingCount)
                ->description('Esperando asignación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($this->getChartData('pending', 7))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-warning-50',
                ]),

            Stat::make('En Tránsito', $inTransitCount)
                ->description('Siendo entregados ahora')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary')
                ->chart($this->getChartData('in_transit', 7))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-primary-50',
                ]),

            Stat::make('Entregados Hoy', $deliveredToday)
                ->description($deliveryTrend >= 0 
                    ? "↗️ +" . number_format($deliveryTrend, 1) . "% vs ayer"
                    : "↘️ " . number_format($deliveryTrend, 1) . "% vs ayer")
                ->descriptionIcon($deliveryTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($this->getChartData('delivered', 7))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-success-50',
                ]),

            Stat::make('Total Activos', $pendingCount + $assignedCount + $inTransitCount)
                ->description('Pedidos en proceso')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('info')
                ->chart($this->getTotalActiveChart(7))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-info-50',
                ]),
        ];
    }

    /**
     * Obtener datos del gráfico para un estado específico
     */
    private function getChartData(string $status, int $days): array
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = DeliveryOrder::whereDate('created_at', $date)
                ->where('status', $status)
                ->count();
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Obtener datos del gráfico para pedidos activos totales
     */
    private function getTotalActiveChart(int $days): array
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = DeliveryOrder::whereDate('created_at', $date)
                ->whereIn('status', ['pending', 'assigned', 'in_transit'])
                ->count();
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Hacer que el widget sea clickeable para filtrar la tabla
     */
    public function getColumns(): int
    {
        return 4;
    }

    /**
     * Configurar el widget para que se actualice automáticamente
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }
}