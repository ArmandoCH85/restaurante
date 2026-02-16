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
            ->label('Abrir Nueva Caja')
            ->icon('heroicon-m-calculator')
            ->color('success')
            ->button()
            ->requiresConfirmation()
            ->modalHeading('Confirmar apertura de caja')
            ->modalDescription('¿Estás seguro de que deseas abrir una nueva caja? Verifica que el monto inicial sea correcto antes de continuar.')
            ->modalSubmitActionLabel('✅ Sí, abrir caja')
            ->modalIcon('heroicon-m-calculator')
            ->successNotificationTitle('Caja abierta exitosamente')
            ->extraAttributes([
                'class' => 'shadow-lg hover:shadow-xl transition-shadow duration-300',
            ]);
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
        $user = Auth::user();

        if (!$user || !$user->hasAnyRole(['cashier', 'admin', 'super_admin'])) {
            Notification::make()
                ->danger()
                ->title('Error al abrir caja')
                ->body('No tienes permiso para realizar una apertura de caja.')
                ->send();

            $this->halt();
        }

        if (CashRegister::hasOpenRegister()) {
            Notification::make()
                ->danger()
                ->title('Error al abrir caja')
                ->body('Ya existe una caja abierta. Cierre la caja actual antes de abrir una nueva.')
                ->send();

            $this->halt();
        }

        return CashRegister::openRegister(
            $data['opening_amount'],
            $data['observations'] ?? null
        );
    }
}
