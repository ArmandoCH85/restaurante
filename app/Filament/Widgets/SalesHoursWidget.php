<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesHoursWidget extends ChartWidget
{
    protected static ?string $heading = '⏰ Horas Pico de Ventas';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '350px';

    // 📐 COLUMNSPAN RESPONSIVO PARA COMPLEMENTAR PAYMENTMETHODS
    protected int | string | array $columnSpan = [
        'default' => 1,  // Móvil: ancho completo
        'sm' => 2,       // Tablet: 2 columnas
        'md' => 2,       // Desktop: 2 de 4 columnas (lado a lado con PaymentMethods)
        'xl' => 3,       // Desktop grande: 3 de 6 columnas
        '2xl' => 4,      // Desktop extra: 4 de 8 columnas
    ];

    // 🔄 FILTRO TEMPORAL
    public ?string $filter = 'last_7_days';

    protected function getData(): array
    {
        $data = $this->getSalesHoursData();

        return [
            'datasets' => [
                [
                    'label' => '💰 Ventas por Hora',
                    'data' => array_values($data['amounts']),
                    'backgroundColor' => array_map(fn($hour, $amount) => $this->getHourColor($hour, $data['peak_hours']),
                                                  array_keys($data['amounts']),
                                                  array_values($data['amounts'])),
                    'borderColor' => '#374151',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => array_keys($data['amounts']),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => '#374151',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'callbacks' => [
                        'title' => "function(context) {
                            const hour = context[0].label;
                            return 'Ventas de ' + hour;
                        }",
                        'label' => "function(context) {
                            const value = context.parsed.y || 0;
                            return 'Total: S/ ' + value.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                        }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => '🕐 Horas del Día',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => '💰 Ventas (S/)',
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'S/ ' + value.toLocaleString('es-PE', { maximumFractionDigits: 0 });
                        }",
                    ],
                ],
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutQuart',
            ],
        ];
    }

    private function getSalesHoursData(): array
    {
        $query = Order::query()
            ->where('billed', true)
            ->select([
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('COUNT(*) as orders_count'),
            ]);

        // 🗓️ APLICAR FILTRO TEMPORAL
        match ($this->filter) {
            'today' => $query->whereDate('created_at', Carbon::today()),
            'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
            'last_7_days' => $query->where('created_at', '>=', Carbon::today()->subDays(7)),
            'last_30_days' => $query->where('created_at', '>=', Carbon::today()->subDays(30)),
            'this_month' => $query->whereMonth('created_at', Carbon::now()->month)
                                 ->whereYear('created_at', Carbon::now()->year),
            'last_month' => $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                                 ->whereYear('created_at', Carbon::now()->subMonth()->year),
            default => $query->where('created_at', '>=', Carbon::today()->subDays(7)),
        };

        $results = $query->groupBy('hour')
                        ->orderBy('hour')
                        ->get();

        // 🕐 CREAR ARRAY CON TODAS LAS HORAS (0-23)
        $amounts = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourLabel = sprintf('%02d:00', $hour);
            $amounts[$hourLabel] = 0;
        }

        // 📊 LLENAR CON DATOS REALES
        foreach ($results as $result) {
            $hourLabel = sprintf('%02d:00', $result->hour);
            $amounts[$hourLabel] = (float) $result->total_sales;
        }

        // 🏆 IDENTIFICAR HORAS PICO (top 3)
        $sortedAmounts = $amounts;
        arsort($sortedAmounts);
        $peakHours = array_slice(array_keys($sortedAmounts), 0, 3, true);

        return [
            'amounts' => $amounts,
            'peak_hours' => $peakHours,
            'total_amount' => array_sum($amounts),
        ];
    }

    private function getHourColor(string $hour, array $peakHours): string
    {
        // 🏆 COLORES ESPECIALES PARA HORAS PICO
        $index = array_search($hour, $peakHours);
        if ($index !== false) {
            return match ($index) {
                0 => '#F59E0B', // 🥇 Oro - Primera hora pico
                1 => '#8B5CF6', // 🥈 Púrpura - Segunda hora pico
                2 => '#10B981', // 🥉 Verde - Tercera hora pico
                default => '#6B7280', // Gris por defecto
            };
        }

        // 🎨 COLORES SEGÚN HORARIO
        $hourNum = (int) substr($hour, 0, 2);

        if ($hourNum >= 6 && $hourNum < 12) {
            return '#F97316'; // 🌅 Naranja - Mañana
        } elseif ($hourNum >= 12 && $hourNum < 18) {
            return '#3B82F6'; // ☀️ Azul - Tarde
        } elseif ($hourNum >= 18 && $hourNum < 24) {
            return '#8B5CF6'; // 🌙 Púrpura - Noche
        } else {
            return '#6B7280'; // 🌃 Gris - Madrugada
        }
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => '📅 Hoy',
            'yesterday' => '📅 Ayer',
            'last_7_days' => '📊 Últimos 7 días',
            'last_30_days' => '📊 Últimos 30 días',
            'this_month' => '📆 Este mes',
            'last_month' => '📆 Mes pasado',
        ];
    }

    // 🔄 ACTUALIZACIÓN AUTOMÁTICA
    protected static ?string $pollingInterval = '120s';

    // 📱 DESCRIPCIÓN PARA MEJOR UX
    protected static ?string $description = 'Identifica las horas de mayor actividad comercial';
}
