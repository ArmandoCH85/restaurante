<?php

namespace App\Filament\Resources\CashRegisterExpenseResource\Pages;

use App\Filament\Resources\CashRegisterExpenseResource;
use App\Models\CashRegister;
use App\Models\CashRegisterExpense;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCashRegisterExpenses extends ListRecords
{
    protected static string $resource = CashRegisterExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Registrar Egreso')
                ->icon('heroicon-m-plus-circle')
                ->color('danger')
                ->size('lg')
                ->button(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['cashRegister'])
            ->latest('created_at');
    }

    public function getTitle(): string
    {
        $openRegister = CashRegister::getOpenRegister();
        if ($openRegister) {
            return 'Egresos - Caja Abierta #' . $openRegister->id;
        }
        return 'Egresos de Caja';
    }
}