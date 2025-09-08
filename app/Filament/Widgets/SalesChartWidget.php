<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use Carbon\Carbon;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;

class SalesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?string $heading = '📈 Tendencia de Ventas por Tipo';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '400px';

    // 📐 ANCHO COMPLETO PARA EL GRÁFICO
    protected int | string | array $columnSpan = 'full';

    // 🔄 REACTIVIDAD A FILTROS DEL DASHBOARD
    protected static bool $isLazy = false;

    protected $listeners = [
        'filtersFormUpdated' => '$refresh',
        'updateCharts' => '$refresh',
    ];

    protected function getData(): array
    {
        $data = $this->getSalesData();

        return [
            'datasets' => [
                [
                    'label' => '🍽️ Mesa',
                    'data' => $data['mesa'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)', // Azul
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => '🚚 Delivery',
                    'data' => $data['delivery'],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)', // Ámbar
                    'borderColor' => 'rgb(245, 158, 11)',
                    'pointBackgroundColor' => 'rgb(245, 158, 11)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => '📱 Apps',
                    'data' => $data['apps'],
                    'backgroundColor' => 'rgba(147, 51, 234, 0.1)', // Púrpura
                    'borderColor' => 'rgb(147, 51, 234)',
                    'pointBackgroundColor' => 'rgb(147, 51, 234)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => '🥡 Venta Directa',
                    'data' => $data['directa'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)', // Verde
                    'borderColor' => 'rgb(34, 197, 94)',
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // 📊 OBTENER DATOS SEGÚN FILTRO DEL DASHBOARD
    private function getSalesData(): array
    {
        $labels = [];
        $mesaData = [];
        $deliveryData = [];
        $directaData = [];
        $appsData = [];

    $dates = $this->expandDailyDates();

        foreach ($dates as $date) {
            $labels[] = $date['label'];

            // 🍽️ VENTAS MESA
            $mesaSales = Order::whereDate('created_at', $date['date'])
                ->where('service_type', 'dine_in')
                ->where('billed', true)
                ->sum('total');
            $mesaData[] = (float) $mesaSales;

            // 🚚 VENTAS DELIVERY
            $deliverySales = Order::whereDate('created_at', $date['date'])
                ->where('service_type', 'delivery')
                ->where('billed', true)
                ->sum('total');
            $deliveryData[] = (float) $deliverySales;

            // 📱 VENTAS APPS (Rappi, Bita Express, etc.)
            $appsSales = Order::whereDate('created_at', $date['date'])
                ->where('billed', true)
                ->whereHas('payments', function($query) {
                    $query->whereIn('payment_method', ['rappi', 'bita_express', 'didi_food', 'pedidos_ya']);
                })
                ->sum('total');
            $appsData[] = (float) $appsSales;

            // 🥡 VENTA DIRECTA (takeout + sin mesa, EXCLUYENDO Apps)
            $directaSales = Order::whereDate('created_at', $date['date'])
                ->where(function($query) {
                    $query->where('service_type', 'takeout')
                          ->orWhere(function($q) {
                              $q->where('service_type', 'dine_in')
                                ->whereNull('table_id');
                          });
                })
                ->where('billed', true)
                ->whereDoesntHave('payments', function($query) {
                    $query->whereIn('payment_method', ['rappi', 'bita_express', 'didi_food', 'pedidos_ya']);
                })
                ->sum('total');
            $directaData[] = (float) $directaSales;
        }

        return [
            'labels' => $labels,
            'mesa' => $mesaData,
            'delivery' => $deliveryData,
            'directa' => $directaData,
            'apps' => $appsData,
        ];
    }

    private function expandDailyDates(): array
    {
        [$start, $end] = $this->resolveDateRange($this->filters ?? []);
        $dates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = [
                'date' => $date->copy(),
                'label' => $start->equalTo($end) ? ($date->isToday() ? 'Hoy' : $date->format('d/m')) : ($start->diffInDays($end) <= 7 ? $date->format('d/m') : $date->format('d')),
            ];
        }
        return $dates;
    }

    // 🎨 OPCIONES AVANZADAS DEL GRÁFICO
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": S/ " + context.parsed.y.toFixed(2);
                        }'
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Período',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Ventas (S/)',
                    ],
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "S/ " + value.toFixed(0);
                        }'
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
