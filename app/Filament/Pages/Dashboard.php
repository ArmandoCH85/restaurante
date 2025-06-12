<?php

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Escritorio';
    protected static ?string $title = 'Escritorio';
    protected static ?int $navigationSort = -1;

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ReservationStats::class,
            \App\Filament\Widgets\SuppliersCountWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        // El rol waiter no puede acceder al Dashboard
        if ($user && $user->hasRole('waiter')) {
            return false;
        }

        return true;
    }
}
