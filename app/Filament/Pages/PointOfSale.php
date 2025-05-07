<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PointOfSale extends Page
{
    // Ocultar esta página del menú de navegación
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.point-of-sale';

    public function mount(): void
    {
        redirect()->to(route('pos.index'));
        //cambio en ventas
    }
}
