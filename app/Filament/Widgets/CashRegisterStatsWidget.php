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

        // Obtener distribución de ventas por método de pago para la caja actual
        $paymentDistribution = [
            $openRegister->cash_sales * 0.2,
            $openRegister->cash_sales * 0.4,
            $openRegister->cash_sales * 0.6,
            $openRegister->cash_sales * 0.8,
            $openRegister->cash_sales,
        ];

        // Verificar si el usuario puede ver información sensible
        $user = auth()->user();
        $canViewSensitiveInfo = $user->hasAnyRole(['admin', 'super_admin', 'manager']);

        return [
            Stat::make('Caja Actual', 'ID: ' . $openRegister->id)
                ->description('Abierta el ' . $openRegister->opening_datetime->format('d/m/Y H:i'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),

            Stat::make('Ventas en Efectivo', $canViewSensitiveInfo
                ? 'S/ ' . number_format($openRegister->cash_sales, 2)
                : 'Información reservada')
                ->description($canViewSensitiveInfo
                    ? 'Total de ventas: S/ ' . number_format($openRegister->total_sales, 2)
                    : 'Solo visible para supervisores')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($canViewSensitiveInfo ? $paymentDistribution : [0, 0, 0, 0, 0]),

            Stat::make('Ventas de Hoy', $canViewSensitiveInfo
                ? 'S/ ' . number_format($todaySales, 2)
                : 'Información reservada')
                ->description('Cajas cerradas hoy: ' . $closedRegistersToday)
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_cash::register');
    }
}
