<?php

namespace App\Filament\Resources\CashRegisterExpenseResource\Pages;

use App\Filament\Resources\CashRegisterExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegisterExpense extends ViewRecord
{
    protected static string $resource = CashRegisterExpenseResource::class;

    public function getTitle(): string
    {
        return 'Ver Detalles del Egreso';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar')
                ->icon('heroicon-m-pencil'),
        ];
    }
}