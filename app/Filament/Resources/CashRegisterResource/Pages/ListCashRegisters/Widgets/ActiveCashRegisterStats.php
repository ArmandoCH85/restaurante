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
        $user = auth()->user();
        $isSupervisor = $user->hasAnyRole(['admin', 'super_admin', 'manager']);

        if (!$openRegister) {
            return [
                Stat::make('Estado de Caja', 'Sin caja activa')
                    ->description('Abra una nueva caja para comenzar operaciones')
                    ->descriptionIcon('heroicon-m-plus-circle')
                    ->color('gray')
                    ->chart([0, 0, 0, 0, 0, 0, 0])
                    ->extraAttributes([
                        'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                    ]),

                Stat::make('Operaciones Hoy', $this->getTodayOperationsCount())
                    ->description('Cajas cerradas en el día')
                    ->descriptionIcon('heroicon-m-archive-box')
                    ->color('info')
                    ->chart($this->getTodayOperationsChart()),

                Stat::make('Tiempo Promedio', $this->getAverageOperationTime())
                    ->description('Duración promedio de operaciones')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),
            ];
        }

        $openedBy = User::find($openRegister->opened_by);
        $openedByName = $openedBy ? $openedBy->name : 'Usuario desconocido';
        $duration = $openRegister->opening_datetime->diffForHumans(null, true);

        return [
            Stat::make('Caja Activa', '#' . $openRegister->id)
                ->description("Abierta hace {$duration} por {$openedByName}")
                ->descriptionIcon('heroicon-m-user')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 6, 7])
                ->extraAttributes([
                    'class' => 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200',
                ]),

            Stat::make('Monto Inicial', $isSupervisor
                ? 'S/ ' . number_format($openRegister->opening_amount, 2)
                : 'Información reservada')
                ->description($isSupervisor
                    ? 'Base para cálculos de cierre'
                    : 'Solo visible para supervisores')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($isSupervisor ? 'info' : 'gray')
                ->chart($isSupervisor ? [1, 1, 1, 1, 1, 1, 1] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('Ventas Efectivo', $isSupervisor
                ? 'S/ ' . number_format($openRegister->cash_sales, 2)
                : 'Información reservada')
                ->description($isSupervisor
                    ? 'Total ventas: S/ ' . number_format($openRegister->total_sales, 2)
                    : 'Solo visible para supervisores')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($isSupervisor ? 'success' : 'gray')
                ->chart($isSupervisor ? $this->getSalesChart($openRegister) : [0, 0, 0, 0, 0, 0, 0])
                ->extraAttributes([
                    'class' => $isSupervisor ? 'bg-gradient-to-r from-blue-50 to-cyan-50 border-blue-200' : '',
                ]),

            Stat::make('📈 Rendimiento', $this->getPerformanceIndicator($openRegister))
                ->description($this->getPerformanceDescription($openRegister))
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($this->getPerformanceColor($openRegister))
                ->chart($this->getPerformanceChart($openRegister)),
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

    protected function getTodayOperationsCount(): string
    {
        $count = CashRegister::whereDate('closing_datetime', today())
            ->where('is_active', false)
            ->count();

        return (string) $count;
    }

    protected function getTodayOperationsChart(): array
    {
        $hours = [];
        for ($i = 8; $i < 20; $i++) { // Horario comercial 8am-8pm
            $count = CashRegister::whereDate('closing_datetime', today())
                ->whereRaw('HOUR(closing_datetime) = ?', [$i])
                ->count();
            $hours[] = $count;
        }

        return $hours;
    }

    protected function getAverageOperationTime(): string
    {
        $avgMinutes = CashRegister::where('opening_datetime', '>=', today()->subDays(7))
            ->where('is_active', false)
            ->whereNotNull('closing_datetime')
            ->get()
            ->avg(function ($register) {
                return $register->opening_datetime->diffInMinutes($register->closing_datetime);
            });

        if (!$avgMinutes) return 'Sin datos';

        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    protected function getPerformanceIndicator($openRegister): string
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return 'Información reservada';
        }

        $hoursOpen = $openRegister->opening_datetime->diffInHours(now());
        if ($hoursOpen == 0) return 'Recién abierta';

        $salesPerHour = $openRegister->total_sales / $hoursOpen;

        if ($salesPerHour > 200) return 'Excelente';
        if ($salesPerHour > 100) return 'Bueno';
        if ($salesPerHour > 50) return 'Regular';
        return 'Bajo';
    }

    protected function getPerformanceDescription($openRegister): string
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return 'Solo visible para supervisores';
        }

        $hoursOpen = $openRegister->opening_datetime->diffInHours(now());
        if ($hoursOpen == 0) return 'Operación iniciada';

        $salesPerHour = $openRegister->total_sales / $hoursOpen;
        return 'S/ ' . number_format($salesPerHour, 2) . ' por hora';
    }

    protected function getPerformanceColor($openRegister): string
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return 'gray';
        }

        $hoursOpen = $openRegister->opening_datetime->diffInHours(now());
        if ($hoursOpen == 0) return 'info';

        $salesPerHour = $openRegister->total_sales / $hoursOpen;

        if ($salesPerHour > 200) return 'success';
        if ($salesPerHour > 100) return 'info';
        if ($salesPerHour > 50) return 'warning';
        return 'danger';
    }

    protected function getPerformanceChart($openRegister): array
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
            return [0, 0, 0, 0, 0, 0, 0];
        }

        $hoursOpen = max(1, $openRegister->opening_datetime->diffInHours(now()));
        $salesPerHour = $openRegister->total_sales / $hoursOpen;
        $maxExpected = 300; // Ventas máximas esperadas por hora

        $performance = min(100, ($salesPerHour / $maxExpected) * 100);

        return [
            $performance * 0.3,
            $performance * 0.5,
            $performance * 0.7,
            $performance * 0.9,
            $performance,
            $performance * 0.8,
            $performance * 0.6,
        ];
    }
}
