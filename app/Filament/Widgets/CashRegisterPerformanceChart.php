<?php

namespace App\Filament\Widgets;

use App\Models\CashRegister;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CashRegisterPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Rendimiento de Cajas Registradoras';
    
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
                        'data' => [0, 0, 0, 0, 0, 0, 0],
                        'borderColor' => '#9ca3af',
                        'fill' => false,
                    ],
                ],
                'labels' => ['Información reservada para supervisores'],
            ];
        }
        
        // Obtener datos de los últimos 7 días
        $dates = collect();
        $totalSales = collect();
        $cashSales = collect();
        $cardSales = collect();
        
        // Generar fechas para los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates->push(Carbon::now()->subDays($i)->format('d/m'));
            
            // Obtener ventas totales para cada día
            $dailySales = CashRegister::whereDate('opening_datetime', $date)
                ->sum('total_sales');
            $totalSales->push($dailySales);
            
            // Obtener ventas en efectivo para cada día
            $dailyCashSales = CashRegister::whereDate('opening_datetime', $date)
                ->sum('cash_sales');
            $cashSales->push($dailyCashSales);
            
            // Obtener ventas con tarjeta para cada día
            $dailyCardSales = CashRegister::whereDate('opening_datetime', $date)
                ->sum('card_sales');
            $cardSales->push($dailyCardSales);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Ventas Totales',
                    'data' => $totalSales->toArray(),
                    'borderColor' => '#3b82f6', // Azul
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Ventas en Efectivo',
                    'data' => $cashSales->toArray(),
                    'borderColor' => '#10b981', // Verde
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Ventas con Tarjeta',
                    'data' => $cardSales->toArray(),
                    'borderColor' => '#f59e0b', // Ámbar
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']);
    }
}
