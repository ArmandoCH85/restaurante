<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesByUserWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Usuario';

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

        // Obtener ventas por usuario
        $salesData = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->join('users', 'orders.employee_id', '=', 'users.id')
            ->select(
                'users.name as user_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('AVG(total) as average_ticket')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Ventas (S/)',
                    'data' => $salesData->pluck('total_sales')->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
                [
                    'label' => 'Ticket Promedio (S/)',
                    'data' => $salesData->pluck('average_ticket')->toArray(),
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $salesData->pluck('user_name')->toArray(),
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
                    'ticks' => [
                        'callback' => "function(value) { return 'S/ ' + value; }",
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) { return context.dataset.label + ': S/ ' + context.raw; }",
                    ],
                ],
            ],
        ];
    }
}
