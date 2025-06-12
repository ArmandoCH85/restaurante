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

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,      // Móvil: 1 columna
            'sm' => 2,           // Tablet: 2 columnas
            'md' => 12,          // Desktop pequeño: 12 columnas (para permitir 5 widgets por fila)
            'lg' => 12,          // Desktop: 12 columnas
            'xl' => 12,          // Desktop grande: 12 columnas
            '2xl' => 12,         // Desktop extra: 12 columnas
        ];
    }
}
