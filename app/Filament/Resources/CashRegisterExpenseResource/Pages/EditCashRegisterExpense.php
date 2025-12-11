<?php

namespace App\Filament\Resources\CashRegisterExpenseResource\Pages;

use App\Filament\Resources\CashRegisterExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashRegisterExpense extends EditRecord
{
    protected static string $resource = CashRegisterExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save_and_continue')
                ->label('Guardar y Continuar Editando')
                ->action('saveAndStay')
                ->color('primary')
                ->icon('heroicon-m-pencil'),

            ...parent::getFormActions(),
        ];
    }

    public function getTitle(): string
    {
        return 'Editar Egreso';
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Egreso actualizado correctamente';
    }

    protected function getSavedNotificationMessage(): string
    {
        $record = $this->record;
        return "Se ha actualizado el egreso de S/ {$record->amount} por concepto: {$record->concept}";
    }
}