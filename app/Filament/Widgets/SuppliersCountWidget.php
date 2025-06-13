<?php

namespace App\Filament\Widgets;

use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuppliersCountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Proveedores', $this->getTotalSuppliers())
                ->description('Proveedores registrados')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success')
                ->chart($this->getChartData()),

            Stat::make('Proveedores Activos', $this->getActiveSuppliers())
                ->description('Proveedores activos')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Últimos 30 días', $this->getNewSuppliers())
                ->description('Nuevos proveedores')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),
        ];
    }

    private function getTotalSuppliers(): int
    {
        return \App\Models\Supplier::count();
    }

    private function getActiveSuppliers(): int
    {
        return \App\Models\Supplier::where('active', true)->count();
    }

    private function getNewSuppliers(): int
    {
        return \App\Models\Supplier::where('created_at', '>=', now()->subDays(30))->count();
    }

    private function getChartData(): array
    {
        return [7, 2, 10, 3, 15, 4, 17];
    }
}
