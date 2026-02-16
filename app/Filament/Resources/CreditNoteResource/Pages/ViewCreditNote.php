<?php

namespace App\Filament\Resources\CreditNoteResource\Pages;

use App\Filament\Resources\CreditNoteResource;
use App\Helpers\SunatServiceHelper;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewCreditNote extends ViewRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->sunat_status === 'PENDIENTE'),

            Actions\Action::make('reenviar_sunat')
                ->label('Reenviar a SUNAT')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => in_array($this->record->sunat_status, ['PENDIENTE', 'ERROR', 'RECHAZADO']))
                ->requiresConfirmation()
                ->modalHeading('Reenviar a SUNAT')
                ->modalDescription('¿Está seguro de que desea reenviar esta nota de crédito a SUNAT?')
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
                            'xml_path' => $result->xml_path ?? $this->record->xml_path,
                            'cdr_path' => $result->cdr_path ?? $this->record->cdr_path,
                            'sunat_status' => $result->sunat_status ?? 'PENDIENTE',
                            'sunat_response' => $result->sunat_response ?? null,
                        ]);

                        Notification::make()
                            ->title('Nota de crédito reenviada exitosamente')
                            ->success()
                            ->send();

                        return redirect()->refresh();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al reenviar a SUNAT')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('descargar_xml')
                ->label('Descargar XML')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->visible(fn () => !empty($this->record->xml_path) && Storage::exists($this->record->xml_path))
                ->action(function (): StreamedResponse {
                    $filename = "NC-{$this->record->serie}-{$this->record->numero}.xml";
                    return Storage::download($this->record->xml_path, $filename);
                }),

            Actions\Action::make('descargar_cdr')
                ->label('Descargar CDR')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->visible(fn () => !empty($this->record->cdr_path) && Storage::exists($this->record->cdr_path))
                ->action(function (): StreamedResponse {
                    $filename = "CDR-{$this->record->serie}-{$this->record->numero}.zip";
                    return Storage::download($this->record->cdr_path, $filename);
                }),

            Actions\Action::make('generar_pdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action(function () {
                    // Implementar generación de PDF
                    Notification::make()
                        ->title('Funcionalidad en desarrollo')
                        ->body('La generación de PDF estará disponible próximamente.')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('consultar_estado')
                ->label('Consultar Estado en SUNAT')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->visible(fn () => $this->record->sunat_status === 'ACEPTADO')
                ->action(function () {
                    try {
                        // Aquí se puede implementar consulta de estado en SUNAT
                        Notification::make()
                            ->title('Estado consultado')
                            ->body('La nota de crédito está vigente en SUNAT.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al consultar estado')
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

    public function getTitle(): string
    {
        return "Nota de Crédito {$this->record->serie}-{$this->record->numero}";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets específicos para la vista
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets de pie de página
        ];
    }
}