<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use App\Models\CashRegister;
use Carbon\Carbon;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;

class SalesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?string $heading = 'Tendencia de Ventas por Canal';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '544px';

    protected static ?string $description = 'Comparativo Diario por Canal';

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
                    'label' => 'Mesa',
                    'data' => $data['mesa'],
                    'backgroundColor' => 'rgba(21, 93, 166, 0.15)',
                    'borderColor' => 'rgb(21, 93, 166)',
                    'pointBackgroundColor' => 'rgb(21, 93, 166)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 1.5,
                    'pointRadius' => 2.5,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.6,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Delivery',
                    'data' => $data['delivery'],
                    'backgroundColor' => 'rgba(180, 83, 9, 0.14)',
                    'borderColor' => 'rgb(180, 83, 9)',
                    'pointBackgroundColor' => 'rgb(180, 83, 9)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 1.5,
                    'pointRadius' => 2.5,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.6,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Apps',
                    'data' => $data['apps'],
                    'backgroundColor' => 'rgba(51, 65, 85, 0.14)',
                    'borderColor' => 'rgb(51, 65, 85)',
                    'pointBackgroundColor' => 'rgb(51, 65, 85)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 1.5,
                    'pointRadius' => 2.5,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.6,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Venta directa',
                    'data' => $data['directa'],
                    'backgroundColor' => 'rgba(5, 150, 105, 0.14)',
                    'borderColor' => 'rgb(5, 150, 105)',
                    'pointBackgroundColor' => 'rgb(5, 150, 105)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 1.5,
                    'pointRadius' => 2.5,
                    'pointHoverRadius' => 5,
                    'borderWidth' => 2.6,
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

            // Determinar si esta fecha es HOY o en el futuro
            $isToday = $date['date']->isToday() || $date['date']->isFuture();

            if ($isToday) {
                // Para hoy: usar órdenes de cajas abiertas o sin caja, manteniendo desglose por tipo
                
                // 🍽️ VENTAS MESA
                $mesaSales = Order::whereDate('created_at', $date['date'])
                    ->where('service_type', 'dine_in')
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_OPEN);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->sum('total');
                $mesaData[] = (float) $mesaSales;

                // 🚚 VENTAS DELIVERY
                $deliverySales = Order::whereDate('created_at', $date['date'])
                    ->where('service_type', 'delivery')
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_OPEN);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->sum('total');
                $deliveryData[] = (float) $deliverySales;

                // 📱 VENTAS APPS (Rappi, Bita Express, etc.)
                $appsSales = Order::whereDate('created_at', $date['date'])
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_OPEN);
                        })
                        ->orWhereNull('cash_register_id');
                    })
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
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_OPEN);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->whereDoesntHave('payments', function($query) {
                        $query->whereIn('payment_method', ['rappi', 'bita_express', 'didi_food', 'pedidos_ya']);
                    })
                    ->sum('total');
                $directaData[] = (float) $directaSales;
                
            } else {
                // Para fechas pasadas: usar órdenes de cajas cerradas, manteniendo desglose por tipo
                
                // 🍽️ VENTAS MESA
                $mesaSales = Order::whereDate('created_at', $date['date'])
                    ->where('service_type', 'dine_in')
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->sum('total');
                $mesaData[] = (float) $mesaSales;

                // 🚚 VENTAS DELIVERY
                $deliverySales = Order::whereDate('created_at', $date['date'])
                    ->where('service_type', 'delivery')
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->sum('total');
                $deliveryData[] = (float) $deliverySales;

                // 📱 VENTAS APPS (Rappi, Bita Express, etc.)
                $appsSales = Order::whereDate('created_at', $date['date'])
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                        })
                        ->orWhereNull('cash_register_id');
                    })
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
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->whereDoesntHave('payments', function($query) {
                        $query->whereIn('payment_method', ['rappi', 'bita_express', 'didi_food', 'pedidos_ya']);
                    })
                    ->sum('total');
                $directaData[] = (float) $directaSales;
            }
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
                    'labels' => [
                        'usePointStyle' => true,
                        'boxWidth' => 10,
                        'padding' => 16,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
                    'borderColor' => 'rgba(148, 163, 184, 0.3)',
                    'borderWidth' => 1,
                    'padding' => 12,
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
                    'grid' => [
                        'display' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Período',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.2)',
                    ],
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
            'animation' => [
                'duration' => 900,
                'easing' => 'easeOutCubic',
            ],
            'elements' => [
                'line' => [
                    'capBezierPoints' => true,
                ],
            ],
            'layout' => [
                'padding' => [
                    'top' => 8,
                    'right' => 12,
                    'bottom' => 4,
                    'left' => 4,
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
