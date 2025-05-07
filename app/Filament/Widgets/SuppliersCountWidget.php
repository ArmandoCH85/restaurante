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
            Stat::make('Proveedores Activos', Supplier::where('active', true)->count())
                ->description('Total de proveedores activos')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
        ];
    }
}
