<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Carbon\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = '📈 Tendencia de Ventas por Tipo';

        protected static ?int $sort = 2;

    protected static ?string $maxHeight = '400px';

    // 📐 ANCHO COMPLETO PARA EL GRÁFICO
    protected int | string | array $columnSpan = 'full';

    // 🔄 FILTRO TEMPORAL
    public ?string $filter = 'last_7_days';

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

    // 🎛️ FILTROS TEMPORALES
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'yesterday' => 'Ayer',
            'last_7_days' => 'Últimos 7 días',
            'last_30_days' => 'Últimos 30 días',
            'this_month' => 'Este mes',
            'last_month' => 'Mes pasado',
        ];
    }

    // 📊 OBTENER DATOS SEGÚN FILTRO
    private function getSalesData(): array
    {
        $labels = [];
        $mesaData = [];
        $deliveryData = [];
        $directaData = [];

        $dates = $this->getDateRange();

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

            // 🥡 VENTA DIRECTA (takeout + sin mesa)
            $directaSales = Order::whereDate('created_at', $date['date'])
                ->where(function($query) {
                    $query->where('service_type', 'takeout')
                          ->orWhere(function($q) {
                              $q->where('service_type', 'dine_in')
                                ->whereNull('table_id');
                          });
                })
                ->where('billed', true)
                ->sum('total');
            $directaData[] = (float) $directaSales;
        }

        return [
            'labels' => $labels,
            'mesa' => $mesaData,
            'delivery' => $deliveryData,
            'directa' => $directaData,
        ];
    }

    // 📅 GENERAR RANGO DE FECHAS SEGÚN FILTRO
    private function getDateRange(): array
    {
        $dates = [];

        switch ($this->filter) {
            case 'today':
                $dates[] = [
                    'date' => Carbon::today(),
                    'label' => 'Hoy',
                ];
                break;

            case 'yesterday':
                $dates[] = [
                    'date' => Carbon::yesterday(),
                    'label' => 'Ayer',
                ];
                break;

            case 'last_7_days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $dates[] = [
                        'date' => $date,
                        'label' => $date->format('d/m'),
                    ];
                }
                break;

            case 'last_30_days':
                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $dates[] = [
                        'date' => $date,
                        'label' => $date->format('d/m'),
                    ];
                }
                break;

            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now();

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dates[] = [
                        'date' => $date->copy(),
                        'label' => $date->format('d'),
                    ];
                }
                break;

            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();

                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $dates[] = [
                        'date' => $date->copy(),
                        'label' => $date->format('d'),
                    ];
                }
                break;
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
