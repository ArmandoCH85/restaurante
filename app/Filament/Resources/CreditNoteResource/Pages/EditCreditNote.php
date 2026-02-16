<?php

namespace App\Filament\Resources\CreditNoteResource\Pages;

use App\Filament\Resources\CreditNoteResource;
use App\Helpers\SunatServiceHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditCreditNote extends EditRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            Actions\Action::make('enviar_sunat')
                ->label('Enviar a SUNAT')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn () => $this->record->sunat_status === 'PENDIENTE')
                ->requiresConfirmation()
                ->modalHeading('Enviar a SUNAT')
                ->modalDescription('¿Está seguro de que desea enviar esta nota de crédito a SUNAT? Una vez enviada, no podrá modificar los datos principales.')
                ->action(function () {
                    try {
                        $sunatService = SunatServiceHelper::createIfNotTesting();
                        if ($sunatService === null) {
                            Notification::make()
                                ->title('Modo testing - SUNAT deshabilitado')
                                ->warning()
                                ->send();
                            return;
                        }
                        $result = $sunatService->emitirNotaCredito(
                            $this->record->invoice,
                            $this->record->motivo_codigo,
                            $this->record->motivo_descripcion
                        );

                        $this->record->update([
                            'xml_path' => $result->xml_path ?? null,
                            'cdr_path' => $result->cdr_path ?? null,
                            'sunat_status' => $result->sunat_status ?? 'PENDIENTE',
                            'sunat_response' => $result->sunat_response ?? null,
                        ]);

                        Notification::make()
                            ->title('Nota de crédito enviada exitosamente')
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.facturacion.notas-credito.view', $this->record);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al enviar a SUNAT')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->sunat_status === 'PENDIENTE')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Nota de Crédito')
                ->modalDescription('¿Está seguro de que desea eliminar esta nota de crédito? Esta acción no se puede deshacer.')
                ->successRedirectUrl(fn () => static::getResource()::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Solo permitir edición si está pendiente
        if ($this->record->sunat_status !== 'PENDIENTE') {
            Notification::make()
                ->title('No se puede editar')
                ->body('Solo se pueden editar notas de crédito con estado PENDIENTE.')
                ->warning()
                ->send();
            
            return $this->record->toArray();
        }

        // Mantener algunos campos que no deben cambiar
        $data['serie'] = $this->record->serie;
        $data['numero'] = $this->record->numero;
        $data['fecha_emision'] = $this->record->fecha_emision;
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Verificar que se puede editar
        if ($record->sunat_status !== 'PENDIENTE') {
            Notification::make()
                ->title('Operación no permitida')
                ->body('Solo se pueden editar notas de crédito con estado PENDIENTE.')
                ->warning()
                ->send();
            
            return $record;
        }

        $record->update($data);

        Notification::make()
            ->title('Nota de crédito actualizada')
            ->body('Los cambios se han guardado correctamente.')
            ->success()
            ->send();

        return $record;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar Cambios')
            ->disabled(fn () => $this->record->sunat_status !== 'PENDIENTE');
    }

    public function getTitle(): string
    {
        return "Editar Nota de Crédito {$this->record->serie}-{$this->record->numero}";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets específicos para la edición
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}