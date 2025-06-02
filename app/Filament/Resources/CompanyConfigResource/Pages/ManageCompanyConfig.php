<?php

namespace App\Filament\Resources\CompanyConfigResource\Pages;

use App\Filament\Resources\CompanyConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    /**
     * Hook que se ejecuta antes de guardar un registro
     */
    protected function beforeSave(): void
    {
        $data = $this->form->getState();
        $record = $this->getRecord();

        // Si estamos editando el RUC, verificar si cambió
        if ($record && $record->key === 'ruc' && isset($data['value'])) {
            $oldRuc = $record->value;
            $newRuc = $data['value'];

            if ($oldRuc !== $newRuc) {
                // Log del cambio de RUC
                Log::warning('Cambio de RUC detectado', [
                    'usuario' => Auth::user()?->name ?? 'Desconocido',
                    'ruc_anterior' => $oldRuc,
                    'ruc_nuevo' => $newRuc,
                    'timestamp' => now(),
                    'ip' => request()->ip()
                ]);
            }
        }
    }

    /**
     * Hook que se ejecuta después de guardar un registro
     */
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record && $record->key === 'ruc') {
            Notification::make()
                ->title('RUC actualizado correctamente')
                ->body('⚠️ Importante: Verifique que el nuevo RUC sea correcto para evitar problemas con SUNAT.')
                ->warning()
                ->duration(8000)
                ->send();
        }
    }
}
