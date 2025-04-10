<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PointOfSale extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Punto de Venta';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Ventas';

    protected static string $view = 'filament.pages.point-of-sale';

    public function mount(): void
    {
        redirect()->to(route('pos.index'));
    }
}
