<?php

namespace App\Filament\Resources\CompanyConfigResource\Pages;

use App\Filament\Resources\CompanyConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageCompanyConfig extends ManageRecords
{
    protected static string $resource = CompanyConfigResource::class;

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
