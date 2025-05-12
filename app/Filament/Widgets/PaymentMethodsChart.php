<?php

namespace App\Filament\Widgets;

use App\Models\CashRegister;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentMethodsChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Ventas por Método de Pago';

    protected static ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Verificar si el usuario puede ver información sensible
        $user = auth()->user();
        $canViewSensitiveInfo = $user->hasAnyRole(['admin', 'super_admin', 'manager']);

        if (!$canViewSensitiveInfo) {
            return [
                'datasets' => [
                    [
                        'label' => 'Información reservada',
                        'data' => [0],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['Información reservada para supervisores'],
            ];
        }

        // Obtener la caja actual
        $openRegister = CashRegister::getOpenRegister();

        if (!$openRegister) {
            // Si no hay caja abierta, mostrar datos de la última semana
            $startDate = now()->subDays(7)->startOfDay();

            $paymentData = Payment::where('payment_datetime', '>=', $startDate)
                ->select('payment_method', DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->mapWithKeys(function ($item) {
                    $methodName = match($item->payment_method) {
                        Payment::METHOD_CASH => 'Efectivo',
                        Payment::METHOD_CREDIT_CARD => 'Tarjeta de Crédito',
                        Payment::METHOD_DEBIT_CARD => 'Tarjeta de Débito',
                        Payment::METHOD_BANK_TRANSFER => 'Transferencia',
                        Payment::METHOD_DIGITAL_WALLET => 'Billetera Digital',
                        default => $item->payment_method,
                    };
                    return [$methodName => $item->total];
                })
                ->toArray();
        } else {
            // Si hay caja abierta, mostrar datos de la caja actual
            $paymentData = [
                'Efectivo' => $openRegister->cash_sales,
                'Tarjeta' => $openRegister->card_sales,
                'Otros' => $openRegister->other_sales,
            ];
        }

        // Filtrar métodos con montos cero
        $paymentData = array_filter($paymentData, fn($amount) => $amount > 0);

        // Si no hay datos, mostrar mensaje
        if (empty($paymentData)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Sin ventas',
                        'data' => [1],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['Sin ventas registradas'],
            ];
        }

        // Colores para cada método de pago
        $colors = [
            'Efectivo' => '#10b981', // Verde
            'Tarjeta de Crédito' => '#3b82f6', // Azul
            'Tarjeta de Débito' => '#6366f1', // Índigo
            'Tarjeta' => '#3b82f6', // Azul
            'Transferencia' => '#8b5cf6', // Violeta
            'Billetera Digital' => '#ec4899', // Rosa
            'Otros' => '#f59e0b', // Ámbar
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Ventas por método de pago',
                    'data' => array_values($paymentData),
                    'backgroundColor' => array_map(fn($method) => $colors[$method] ?? '#9ca3af', array_keys($paymentData)),
                ],
            ],
            'labels' => array_keys($paymentData),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_cash::register');
    }
}
