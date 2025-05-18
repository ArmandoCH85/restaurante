<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesByHourChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Hora';
    
    protected static ?string $pollingInterval = '60s';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    // Filtro de fecha
    public ?string $filter = 'today';
    
    public function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'yesterday' => 'Ayer',
            'week' => 'Esta semana',
        ];
    }
    
    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        // Configurar fechas según el filtro
        $startDate = match ($activeFilter) {
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
            'week' => Carbon::now()->startOfWeek(),
            default => Carbon::today(),
        };
        
        $endDate = match ($activeFilter) {
            'today' => Carbon::today()->endOfDay(),
            'yesterday' => Carbon::yesterday()->endOfDay(),
            'week' => Carbon::now(),
            default => Carbon::today()->endOfDay(),
        };
        
        // Obtener ventas por hora
        $hourlyData = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select(
                DB::raw('HOUR(order_datetime) as hour'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        // Preparar datos para el gráfico
        $hours = range(0, 23);
        $hourlyTotals = array_fill(0, 24, 0);
        $hourlyCounts = array_fill(0, 24, 0);
        
        foreach ($hourlyData as $data) {
            $hourlyTotals[$data->hour] = $data->total;
            $hourlyCounts[$data->hour] = $data->count;
        }
        
        // Formatear horas
        $hourLabels = array_map(function ($hour) {
            return sprintf('%02d:00', $hour);
        }, $hours);
        
        return [
            'datasets' => [
                [
                    'label' => 'Ventas (S/)',
                    'data' => $hourlyTotals,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.6)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Número de órdenes',
                    'data' => $hourlyCounts,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 1,
                    'type' => 'line',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $hourLabels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Ventas (S/)',
                    ],
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Número de órdenes',
                    ],
                ],
            ],
        ];
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
