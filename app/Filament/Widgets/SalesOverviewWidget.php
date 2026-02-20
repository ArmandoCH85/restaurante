<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\CashRegister;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Obtener datos para hoy
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Ventas de hoy - Solo de cajas ABIERTAS (o sin caja asignada)
        $todaySales = Order::whereDate('order_datetime', $today)
            ->where('billed', true)
            ->where(function($q) {
                $q->whereHas('cashRegister', function ($subQ) {
                    $subQ->where('is_active', CashRegister::STATUS_OPEN);
                })
                ->orWhereNull('cash_register_id');
            })
            ->sum('total');
            
        // Ventas de ayer - Usar total_sales de cajas CERRADAS
        $yesterdaySales = CashRegister::whereDate('closing_datetime', $yesterday)
            ->where('is_active', CashRegister::STATUS_CLOSED)
            ->sum('total_sales');
            
        // Calcular incremento/decremento
        $salesDiff = $yesterdaySales > 0 
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 
            : 100;
            
        // Ventas de la semana - Combinar cajas ABIERTAS (hoy) + CERRADAS (fechas pasadas)
        $weekSalesFromClosedRegisters = CashRegister::whereBetween('closing_datetime', [$startOfWeek, Carbon::yesterday()->endOfDay()])
            ->where('is_active', CashRegister::STATUS_CLOSED)
            ->sum('total_sales');
            
        $weekSalesFromOpenRegisters = Order::whereDate('order_datetime', $today)
            ->where('billed', true)
            ->where(function($q) {
                $q->whereHas('cashRegister', function ($subQ) {
                    $subQ->where('is_active', CashRegister::STATUS_OPEN);
                })
                ->orWhereNull('cash_register_id');
            })
            ->sum('total');
            
        $weekSales = $weekSalesFromClosedRegisters + $weekSalesFromOpenRegisters;
            
        // Ventas del mes - Combinar cajas ABIERTAS (hoy) + CERRADAS (fechas pasadas)
        $monthSalesFromClosedRegisters = CashRegister::whereBetween('closing_datetime', [$startOfMonth, Carbon::yesterday()->endOfDay()])
            ->where('is_active', CashRegister::STATUS_CLOSED)
            ->sum('total_sales');
            
        $monthSalesFromOpenRegisters = Order::whereDate('order_datetime', $today)
            ->where('billed', true)
            ->where(function($q) {
                $q->whereHas('cashRegister', function ($subQ) {
                    $subQ->where('is_active', CashRegister::STATUS_OPEN);
                })
                ->orWhereNull('cash_register_id');
            })
            ->sum('total');
            
        $monthSales = $monthSalesFromClosedRegisters + $monthSalesFromOpenRegisters;
            
        // Obtener tendencia de ventas para los últimos 7 días
        $salesTrend = $this->getSalesTrend();
        
        return [
            Stat::make('Ventas de Hoy', 'S/ ' . number_format($todaySales, 2))
                ->description($salesDiff >= 0 
                    ? number_format(abs($salesDiff), 1) . '% de incremento' 
                    : number_format(abs($salesDiff), 1) . '% de decremento')
                ->descriptionIcon($salesDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesDiff >= 0 ? 'success' : 'danger')
                ->chart($salesTrend),
                
            Stat::make('Ventas de la Semana', 'S/ ' . number_format($weekSales, 2))
                ->description('Desde el ' . $startOfWeek->format('d/m/Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
                
            Stat::make('Ventas del Mes', 'S/ ' . number_format($monthSales, 2))
                ->description('Desde el ' . $startOfMonth->format('d/m/Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
    
    private function getSalesTrend(): array
    {
        $sales = [];
        
        // Obtener ventas de los últimos 7 días, evaluando cada día individualmente
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            if ($date->isToday()) {
                // Para hoy: usar órdenes de cajas abiertas o sin caja
                $dailySales = Order::whereDate('order_datetime', $date)
                    ->where('billed', true)
                    ->where(function($q) {
                        $q->whereHas('cashRegister', function ($subQ) {
                            $subQ->where('is_active', CashRegister::STATUS_OPEN);
                        })
                        ->orWhereNull('cash_register_id');
                    })
                    ->sum('total');
            } else {
                // Para fechas pasadas: usar total_sales de cajas cerradas
                $dailySales = CashRegister::whereDate('closing_datetime', $date)
                    ->where('is_active', CashRegister::STATUS_CLOSED)
                    ->sum('total_sales');
            }
            
            $sales[] = $dailySales;
        }
        
        return $sales;
    }
}
