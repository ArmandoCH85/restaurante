<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Payment;
use Carbon\Carbon;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;

class PaymentMethodsWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?string $heading = '💳 Distribución de Métodos de Pago';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '350px';

    // 📐 COLUMNSPAN RESPONSIVO SEGÚN MEJORES PRÁCTICAS FILAMENT
    protected int | string | array $columnSpan = [
        'default' => 1,  // Móvil: ancho completo
        'sm' => 2,       // Tablet: 2 columnas
        'md' => 2,       // Desktop: 2 de 4 columnas
        'xl' => 3,       // Desktop grande: 3 de 6 columnas
        '2xl' => 4,      // Desktop extra: 4 de 8 columnas
    ];

    // 🔄 REACTIVIDAD A FILTROS DEL DASHBOARD
    protected static bool $isLazy = false;

    protected $listeners = [
        'filtersFormUpdated' => '$refresh',
        'updateCharts' => '$refresh',
    ];

    protected function getData(): array
    {
        $data = $this->getPaymentMethodsData();

        return [
            'datasets' => [
                [
                    'data' => array_values($data['amounts']),
                    'backgroundColor' => [
                        '#10B981', // 💚 Verde - Efectivo
                        '#3B82F6', // 💙 Azul - Tarjetas
                        '#F59E0B', // 🟡 Ámbar - Yape
                        '#8B5CF6', // 💜 Púrpura - Plin
                        '#EF4444', // ❤️ Rojo - Pedidos Ya
                        '#F97316', // 🟠 Naranja - Didi Food
                        '#06B6D4', // 💙 Cyan - Bita Express
                        '#EC4899', // 🩷 Rosa - Rappi
                        '#6B7280', // ⚫ Gris - Transferencias
                    ],
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => array_keys($data['amounts']),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => '#374151',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
                        // 💰 FORMATO DE TOOLTIPS CON TRANSACCIONES
                        'label' => "function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return [
                                label + ': S/ ' + value.toLocaleString('es-PE', { minimumFractionDigits: 2 }),
                                'Participación: ' + percentage + '%'
                            ];
                        }",
                    ],
                ],
            ],
            'cutout' => '60%', // 🍩 HACE EL AGUJERO DEL CENTRO
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
                'duration' => 1000,
            ],
        ];
    }

    private function getPaymentMethodsData(): array
    {
        $query = Payment::query()
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.billed', true);

        [$start, $end] = $this->resolveDateRange($this->filters ?? []);
        $query->whereBetween('payments.created_at', [$start, $end]);

        $payments = $query->get();

        $amounts = [
            '💵 Efectivo' => 0,
            '💳 Tarjetas' => 0,
            '📱 Yape' => 0,
            '💙 Plin' => 0,
            '🛵 Pedidos Ya' => 0,
            '🚗 Didi Food' => 0,
            '🚚 Bita Express' => 0,
            '🛵 Rappi' => 0,
            '🏦 Transferencias' => 0,
        ];

        foreach ($payments as $payment) {
            $amount = (float) $payment->amount;

            // 🏷️ CATEGORIZAR MÉTODOS DE PAGO
            if ($payment->payment_method === 'cash') {
                $amounts['💵 Efectivo'] += $amount;
            } elseif (in_array($payment->payment_method, ['credit_card', 'debit_card', 'card'])) {
                $amounts['💳 Tarjetas'] += $amount;
            } elseif ($payment->payment_method === 'digital_wallet') {
                // 📱 DETECTAR YAPE VS PLIN POR REFERENCIA
                if ($payment->reference_number && strpos($payment->reference_number, 'Tipo: yape') !== false) {
                    $amounts['📱 Yape'] += $amount;
                } elseif ($payment->reference_number && strpos($payment->reference_number, 'Tipo: plin') !== false) {
                    $amounts['💙 Plin'] += $amount;
                } else {
                    $amounts['📱 Yape'] += $amount; // Por defecto
                }
            } elseif ($payment->payment_method === 'yape') {
                $amounts['📱 Yape'] += $amount;
            } elseif ($payment->payment_method === 'plin') {
                $amounts['💙 Plin'] += $amount;
            } elseif ($payment->payment_method === 'pedidos_ya') {
                $amounts['🛵 Pedidos Ya'] += $amount;
            } elseif ($payment->payment_method === 'didi_food') {
                $amounts['🚗 Didi Food'] += $amount;
            } elseif ($payment->payment_method === 'bita_express') {
                $amounts['🚚 Bita Express'] += $amount;
            } elseif ($payment->payment_method === 'rappi') {
                $amounts['🛵 Rappi'] += $amount;
            } elseif (in_array($payment->payment_method, ['bank_transfer', 'transfer'])) {
                $amounts['🏦 Transferencias'] += $amount;
            }
        }

        // 🚮 ELIMINAR MÉTODOS CON 0 VENTAS
        $amounts = array_filter($amounts, fn($amount) => $amount > 0);

        // 📊 AÑADIR ESTADÍSTICAS EXTRA
        $totalAmount = array_sum($amounts);
        $totalTransactions = $payments->count();

        return [
            'amounts' => $amounts,
            'total_amount' => $totalAmount,
            'total_transactions' => $totalTransactions,
        ];
    }

    // 🔄 ACTUALIZACIÓN AUTOMÁTICA
    protected static ?string $pollingInterval = '60s';

    // 📱 DESCRIPCIÓN PARA MEJOR UX
    protected static ?string $description = 'Distribución de ingresos por método de pago';
}
