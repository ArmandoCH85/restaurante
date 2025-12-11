<?php

namespace App\Filament\Resources\CashRegisterExpenseResource\Pages;

use App\Filament\Resources\CashRegisterExpenseResource;
use App\Models\CashRegister;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCashRegisterExpense extends CreateRecord
{
    protected static string $resource = CashRegisterExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se especifica caja, usar la caja abierta actual
        if (!isset($data['cash_register_id'])) {
            $openRegister = CashRegister::getOpenRegister();
            if ($openRegister) {
                $data['cash_register_id'] = $openRegister->id;
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save_and_new')
                ->label('Guardar y Nuevo')
                ->action(function () {
                    $this->create();
                    $this->form->fill();
                })
                ->color('primary')
                ->icon('heroicon-m-plus'),

            ...parent::getFormActions(),
        ];
    }

    public function getTitle(): string
    {
        return 'Registrar Nuevo Egreso';
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Egreso registrado correctamente';
    }

    protected function getCreatedNotificationMessage(): string
    {
        $record = $this->record;
        return "Se ha registrado un egreso de S/ {$record->amount} por concepto: {$record->concept}";
    }
}