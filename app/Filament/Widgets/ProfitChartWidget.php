<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProfitChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ganancias vs. Ventas (Costos estimados)';

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

        // Formato de agrupación según el filtro
        $groupFormat = match ($activeFilter) {
            'day' => '%H:00', // Por hora
            'week' => '%Y-%m-%d', // Por día
            'month' => '%Y-%m-%d', // Por día
            'year' => '%Y-%m', // Por mes
            default => '%Y-%m-%d',
        };

        $dateColumn = DB::raw("DATE_FORMAT(order_datetime, '{$groupFormat}') as date");

        // Obtener ventas
        $sales = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select($dateColumn, DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Obtener costos aproximados (basados en el costo actual de los productos)
        $costs = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_datetime', [$startDate, $endDate])
                    ->where('billed', true);
            })
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(
                DB::raw("DATE_FORMAT(orders.order_datetime, '{$groupFormat}') as date"),
                DB::raw('SUM(order_details.quantity * products.current_cost) as cost')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('cost', 'date')
            ->toArray();

        // Calcular ganancias
        $profits = [];
        $labels = array_unique(array_merge(array_keys($sales), array_keys($costs)));
        sort($labels);

        foreach ($labels as $label) {
            $sale = $sales[$label] ?? 0;
            $cost = $costs[$label] ?? 0;
            $profits[$label] = $sale - $cost;
        }

        // Formatear etiquetas según el filtro
        $formattedLabels = array_map(function ($label) use ($activeFilter) {
            return match ($activeFilter) {
                'day' => $label, // Hora
                'week', 'month' => Carbon::createFromFormat('Y-m-d', $label)->format('d/m'), // Día/Mes
                'year' => Carbon::createFromFormat('Y-m', $label)->format('M Y'), // Mes Año
                default => $label,
            };
        }, $labels);

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => array_values(array_intersect_key($sales, array_flip($labels))),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)', // Azul
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Ganancias',
                    'data' => array_values($profits),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)', // Verde
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $formattedLabels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['super_admin', 'admin']);
    }
}
