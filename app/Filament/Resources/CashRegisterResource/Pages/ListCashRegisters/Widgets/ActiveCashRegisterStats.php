<?php

namespace App\Filament\Resources\CashRegisterResource\Pages\ListCashRegisters\Widgets;

use App\Models\CashRegister;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ActiveCashRegisterStats extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $openRegister = CashRegister::getOpenRegister();

        if (!$openRegister) {
            return [
                Stat::make('Estado de Caja', 'No hay caja abierta')
                    ->description('Puede abrir una nueva caja usando el botón "Abrir Caja"')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('danger')
                    ->chart([0, 0, 0, 0, 0, 0, 0]),
            ];
        }

        $openedBy = User::find($openRegister->opened_by);
        $openedByName = $openedBy ? $openedBy->name : 'Usuario desconocido';

        return [
            Stat::make('Caja Activa', 'ID: ' . $openRegister->id)
                ->description('Abierta el ' . $openRegister->opening_datetime->format('d/m/Y H:i'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->chart([1, 1, 1, 1, 1, 1, 1]),

            Stat::make('Abierta por', $openedByName)
                ->description('Monto inicial: S/ ' . number_format($openRegister->opening_amount, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(
                $this->getSalesLabel($openRegister),
                $this->getSalesValue($openRegister)
            )
                ->description($this->getSalesDescription($openRegister))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getSalesChart($openRegister)),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()->can('view_any_cash::register');
    }

    protected function getSalesLabel($openRegister): string
    {
        return 'Ventas en Efectivo';
    }

    protected function getSalesValue($openRegister): string
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return 'S/ ' . number_format($openRegister->cash_sales, 2);
        } else {
            return 'Información reservada';
        }
    }

    protected function getSalesDescription($openRegister): string
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return 'Total de ventas: S/ ' . number_format($openRegister->total_sales, 2);
        } else {
            return 'Solo visible para supervisores';
        }
    }

    protected function getSalesChart($openRegister): array
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return [
                $openRegister->cash_sales * 0.2,
                $openRegister->cash_sales * 0.4,
                $openRegister->cash_sales * 0.6,
                $openRegister->cash_sales * 0.8,
                $openRegister->cash_sales,
            ];
        } else {
            return [0, 0, 0, 0, 0];
        }
    }
}
