<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateCashRegister extends CreateRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Abrir Caja')
            ->icon('heroicon-o-banknotes')
            ->requiresConfirmation()
            ->modalHeading('Confirmar apertura de caja')
            ->modalDescription('¿Estás seguro de que deseas abrir una nueva caja? Verifica que el monto inicial sea correcto.')
            ->modalSubmitActionLabel('Sí, abrir caja')
            ->modalIcon('heroicon-o-banknotes')
            ->successNotificationTitle('Caja abierta correctamente');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['opened_by'] = Auth::id();
        $data['opening_datetime'] = now();
        $data['is_active'] = true;
        $data['cash_sales'] = 0;
        $data['card_sales'] = 0;
        $data['other_sales'] = 0;
        $data['total_sales'] = 0;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Verificar si el usuario tiene permiso para abrir caja
        $user = Auth::user();
        $hasPermission = $user && $user->hasAnyRole(['cashier', 'admin', 'super_admin']);

        if (!$hasPermission) {
            Notification::make()
                ->danger()
                ->title('Error al abrir caja')
                ->body('No tienes permiso para realizar una apertura de caja.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return new CashRegister(); // Nunca se ejecutará debido al redirect
        }

        // Verificar si ya existe una caja abierta
        if (CashRegister::hasOpenRegister()) {
            Notification::make()
                ->danger()
                ->title('Error al abrir caja')
                ->body('Ya existe una caja abierta. Cierre la caja actual antes de abrir una nueva.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return new CashRegister(); // Nunca se ejecutará debido al redirect
        }

        // Crear la caja usando el método estático
        return CashRegister::openRegister(
            $data['opening_amount'],
            $data['observations'] ?? null
        );
    }
}
