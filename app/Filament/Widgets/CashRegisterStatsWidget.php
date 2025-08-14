<?php

namespace App\Filament\Widgets;

use App\Models\CashRegister;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CashRegisterStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Verificar si hay una caja abierta
        $openRegister = CashRegister::getOpenRegister();

        if (!$openRegister) {
            return [
                Stat::make('Estado de Caja', 'No hay caja abierta')
                    ->description('Puede abrir una nueva caja desde el módulo de Cajas Registradoras')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('danger'),

                Stat::make('Ventas de Hoy', 'S/ 0.00')
                    ->description('No hay ventas registradas hoy')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('gray'),

                Stat::make('Cajas Cerradas Hoy', '0')
                    ->description('No hay cajas cerradas hoy')
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('gray'),
            ];
        }

        // Obtener estadísticas de ventas de hoy
        $today = now()->startOfDay();
        $todaySales = Payment::where('payment_datetime', '>=', $today)
                            ->sum('amount');

        // Obtener estadísticas de cajas cerradas hoy
        $closedRegistersToday = CashRegister::where('closing_datetime', '>=', $today)
                                        ->where('is_active', false)
                                        ->count();

        // Distribución real por método de pago de pagos asociados a esta caja
        $byMethod = Payment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->where('cash_register_id', $openRegister->id)
            ->groupBy('payment_method')
            ->pluck('total','payment_method');
        $cashSales = (float) ($byMethod['cash'] ?? 0);
        $cardSales = (float) (($byMethod['card'] ?? 0) + ($byMethod['credit_card'] ?? 0) + ($byMethod['debit_card'] ?? 0));
        $transferSales = (float) ($byMethod['bank_transfer'] ?? 0);
        $walletSales = (float) ($byMethod['digital_wallet'] ?? 0);
        $otherSales = (float) $openRegister->other_sales;
        $paymentDistribution = array_values(array_filter([
            $cashSales,
            $cardSales,
            $walletSales,
            $transferSales,
            $otherSales,
        ], fn($v) => $v > 0));

        // Verificar si el usuario puede ver información sensible
        $user = auth()->user();
        $canViewSensitiveInfo = $user->hasAnyRole(['admin', 'super_admin', 'manager']);

        return [
            Stat::make('Caja Actual', 'ID: ' . $openRegister->id)
                ->description('Abierta el ' . $openRegister->opening_datetime->format('d/m/Y H:i'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('Ventas Caja (Efectivo)', $canViewSensitiveInfo
                ? 'S/ ' . number_format($cashSales, 2)
                : 'Reservado')
                ->description($canViewSensitiveInfo
                    ? 'Total caja: S/ ' . number_format($openRegister->total_sales, 2)
                    : 'Solo supervisores')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($canViewSensitiveInfo ? $paymentDistribution : []),

            Stat::make('Ventas de Hoy', $canViewSensitiveInfo
                ? 'S/ ' . number_format($todaySales, 2)
                : 'Reservado')
                ->description('Cerradas hoy: ' . $closedRegistersToday)
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_cash::register');
    }
}
