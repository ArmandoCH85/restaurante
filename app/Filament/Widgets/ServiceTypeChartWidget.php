<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ServiceTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Tipo de Servicio';
    
    protected static ?string $pollingInterval = '60s';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';
    
    // Filtro de fecha
    public ?string $filter = 'week';
    
    public function getFilters(): ?array
    {
        return [
            'day' => 'Hoy',
            'week' => 'Esta semana',
            'month' => 'Este mes',
            'year' => 'Este año',
        ];
    }
    
    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        // Configurar fechas según el filtro
        $startDate = match ($activeFilter) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek(),
        };
        
        $endDate = Carbon::now();
        
        // Obtener ventas por tipo de servicio
        $salesByServiceType = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select('service_type', DB::raw('SUM(total) as total'))
            ->groupBy('service_type')
            ->get()
            ->pluck('total', 'service_type')
            ->toArray();
            
        // Mapear nombres de tipos de servicio
        $serviceTypeLabels = [
            'dine_in' => 'En Local',
            'takeout' => 'Para Llevar',
            'delivery' => 'Delivery',
        ];
        
        // Colores para cada tipo de servicio
        $colors = [
            'dine_in' => '#4f46e5', // Indigo
            'takeout' => '#f59e0b', // Amber
            'delivery' => '#10b981', // Emerald
        ];
        
        // Preparar datos para el gráfico
        $labels = [];
        $data = [];
        $backgroundColor = [];
        
        foreach ($serviceTypeLabels as $key => $label) {
            if (isset($salesByServiceType[$key]) && $salesByServiceType[$key] > 0) {
                $labels[] = $label;
                $data[] = $salesByServiceType[$key];
                $backgroundColor[] = $colors[$key] ?? '#9ca3af'; // Gris por defecto
            }
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Ventas por tipo de servicio',
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
