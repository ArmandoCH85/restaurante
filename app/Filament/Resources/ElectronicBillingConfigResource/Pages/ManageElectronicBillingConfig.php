<?php

namespace App\Filament\Resources\ElectronicBillingConfigResource\Pages;

use App\Filament\Resources\ElectronicBillingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageElectronicBillingConfig extends ManageRecords
{
    protected static string $resource = ElectronicBillingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Guardar Cambios')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action(function () {
                    Notification::make()
                        ->title('Configuración guardada')
                        ->success()
                        ->send();

                    // Recargar la página para mostrar los cambios
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}
