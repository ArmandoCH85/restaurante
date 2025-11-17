<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PurchaseStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        // Obtener todas las estadísticas de compras
        $totalPurchases = Purchase::count();
        $completedPurchases = Purchase::where('status', 'completed')->count();
        $pendingPurchases = Purchase::where('status', 'pending')->count();
        
        // Calcular el valor total de compras
        $totalAmount = Purchase::sum('total');
        $completedAmount = Purchase::where('status', 'completed')->sum('total');
        $pendingAmount = Purchase::where('status', 'pending')->sum('total');
        
        return [
            Stat::make('💎 Valor Total de Compras', 'S/ ' . number_format($totalAmount, 2, '.', ','))
                ->description('Monto total invertido en compras')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make(' Total de Compras', number_format($totalPurchases, 0, '.', ','))
                ->description('Todas las compras registradas')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart([5, 3, 8, 2, 12, 4, 9]),

            Stat::make('✅ Compras Finalizadas', number_format($completedPurchases, 0, '.', ','))
                ->description($totalPurchases > 0
                    ? round(($completedPurchases / $totalPurchases) * 100, 1) . '% del total'
                    : '0% del total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([2, 4, 6, 8, 10, 8, 12]),

            Stat::make('⏳ Compras Pendientes', number_format($pendingPurchases, 0, '.', ','))
                ->description($totalPurchases > 0
                    ? round(($pendingPurchases / $totalPurchases) * 100, 1) . '% del total'
                    : '0% del total')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([1, 3, 2, 5, 3, 4, 2]),
        ];
    }
}