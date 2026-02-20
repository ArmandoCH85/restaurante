<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Payment;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;
use Illuminate\Support\Facades\DB;

class PaymentMethodsWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?string $heading = 'Distribución por Método de Pago';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '544px';

    protected int | string | array $columnSpan = 'full';

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
                        '#0F4C81',
                        '#1D4ED8',
                        '#D97706',
                        '#0E7490',
                        '#DC2626',
                        '#C2410C',
                        '#0891B2',
                        '#7C3AED',
                        '#475569',
                    ],
                    'borderWidth' => 1.5,
                    'borderColor' => '#ffffff',
                    'hoverBorderColor' => '#f8fafc',
                    'hoverOffset' => 12,
                    'spacing' => 2,
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
                        'padding' => 14,
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#ffffff',
                    'borderColor' => 'rgba(148, 163, 184, 0.35)',
                    'borderWidth' => 1,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'callbacks' => [
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
            'cutout' => '56%',
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true,
                'duration' => 950,
                'easing' => 'easeOutCubic',
            ],
            'layout' => [
                'padding' => [
                    'top' => 8,
                    'right' => 6,
                    'bottom' => 8,
                    'left' => 6,
                ],
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

        $payments = (clone $query)
            ->select([
                'payments.payment_method',
                'payments.reference_number',
                DB::raw('SUM(payments.amount) as total_amount'),
            ])
            ->groupBy('payments.payment_method', 'payments.reference_number')
            ->get();

        $amounts = [
            'Efectivo' => 0,
            'Tarjetas' => 0,
            'Yape' => 0,
            'Plin' => 0,
            'Pedidos Ya' => 0,
            'Didi Food' => 0,
            'Bita Express' => 0,
            'Rappi' => 0,
            'Transferencias' => 0,
        ];

        foreach ($payments as $payment) {
            $amount = (float) $payment->total_amount;

            if ($payment->payment_method === 'cash') {
                $amounts['Efectivo'] += $amount;
            } elseif (in_array($payment->payment_method, ['credit_card', 'debit_card', 'card'])) {
                $amounts['Tarjetas'] += $amount;
            } elseif ($payment->payment_method === 'digital_wallet') {
                if ($payment->reference_number && strpos($payment->reference_number, 'Tipo: yape') !== false) {
                    $amounts['Yape'] += $amount;
                } elseif ($payment->reference_number && strpos($payment->reference_number, 'Tipo: plin') !== false) {
                    $amounts['Plin'] += $amount;
                } else {
                    $amounts['Yape'] += $amount;
                }
            } elseif ($payment->payment_method === 'yape') {
                $amounts['Yape'] += $amount;
            } elseif ($payment->payment_method === 'plin') {
                $amounts['Plin'] += $amount;
            } elseif ($payment->payment_method === 'pedidos_ya') {
                $amounts['Pedidos Ya'] += $amount;
            } elseif ($payment->payment_method === 'didi_food') {
                $amounts['Didi Food'] += $amount;
            } elseif ($payment->payment_method === 'bita_express') {
                $amounts['Bita Express'] += $amount;
            } elseif ($payment->payment_method === 'rappi') {
                $amounts['Rappi'] += $amount;
            } elseif (in_array($payment->payment_method, ['bank_transfer', 'transfer'])) {
                $amounts['Transferencias'] += $amount;
            }
        }

        $amounts = array_filter($amounts, fn($amount) => $amount > 0);

        $totalAmount = array_sum($amounts);
        $totalTransactions = (int) $query->count();

        return [
            'amounts' => $amounts,
            'total_amount' => $totalAmount,
            'total_transactions' => $totalTransactions,
        ];
    }

    protected static ?string $pollingInterval = '5m';

    protected static ?string $description = 'Distribución de Ingresos por Método de Pago';
}
