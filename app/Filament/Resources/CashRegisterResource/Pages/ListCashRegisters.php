<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Resources\Pages\ListRecords;

class ListCashRegisters extends ListRecords
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        // PRINCIPIO KISS: Eliminar duplicaciones
        // El Resource ya maneja todas las acciones necesarias
        // Una sola fuente de verdad para mejor UX
        return [];
    }

    public function getHeading(): string
    {
        $openRegister = CashRegister::getOpenRegister();

        if ($openRegister) {
            return 'Apertura y Cierre de Caja - Hay una caja abierta (ID: ' . $openRegister->id . ')';
        }

        return 'Apertura y Cierre de Caja';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ListCashRegisters\Widgets\ActiveCashRegisterStats::class,
        ];
    }
}
