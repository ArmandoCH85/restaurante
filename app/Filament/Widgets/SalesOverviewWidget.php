<?php

namespace App\Filament\Widgets;

use App\Models\Order;
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

        // Ventas de hoy
        $todaySales = Order::whereDate('order_datetime', $today)
            ->where('billed', true)
            ->sum('total');
            
        // Ventas de ayer
        $yesterdaySales = Order::whereDate('order_datetime', $yesterday)
            ->where('billed', true)
            ->sum('total');
            
        // Calcular incremento/decremento
        $salesDiff = $yesterdaySales > 0 
            ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 
            : 100;
            
        // Ventas de la semana
        $weekSales = Order::whereBetween('order_datetime', [$startOfWeek, Carbon::now()])
            ->where('billed', true)
            ->sum('total');
            
        // Ventas del mes
        $monthSales = Order::whereBetween('order_datetime', [$startOfMonth, Carbon::now()])
            ->where('billed', true)
            ->sum('total');
            
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
        // Obtener ventas de los últimos 7 días
        $sales = Order::where('billed', true)
            ->where('order_datetime', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get([
                DB::raw('DATE(order_datetime) as date'),
                DB::raw('SUM(total) as total')
            ])
            ->pluck('total')
            ->toArray();
            
        // Si hay menos de 7 días de datos, rellenar con ceros
        if (count($sales) < 7) {
            $sales = array_pad($sales, 7, 0);
        }
        
        return $sales;
    }
}
