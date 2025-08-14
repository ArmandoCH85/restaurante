<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar')
                ->visible(fn () =>
                    // Misma lógica que en la tabla: Notas de Venta siempre, Factura/Boleta mientras no aceptada/anulada
                    ($this->record->invoice_type === 'sales_note') ||
                    (in_array($this->record->invoice_type, ['invoice','receipt']) && !in_array($this->record->sunat_status, ['ACEPTADO']) && $this->record->tax_authority_status !== 'voided')
                ),
            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('filament.admin.invoices.print-ticket', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('void')
                ->label('Anular')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->canBeVoided())
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Motivo de Anulación')
                        ->required()
                        ->minLength(5)
                        ->maxLength(255),
                    \Filament\Forms\Components\Checkbox::make('confirm')
                        ->label('Confirmo que deseo anular este comprobante y entiendo que esta acción no se puede deshacer.')
                        ->required()
                        ->default(false),
                ])
                ->requiresConfirmation()
                ->modalHeading('Anular Comprobante')
                ->modalDescription('Esta acción es irreversible. El comprobante quedará registrado como anulado tanto en el sistema como en SUNAT.')
                ->modalSubmitActionLabel('Anular Comprobante')
                ->action(function (array $data): void {
                    if (!$data['confirm']) {
                        \Filament\Notifications\Notification::make()
                            ->title('Debe confirmar la anulación')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (!$this->record->canBeVoided()) {
                        \Filament\Notifications\Notification::make()
                            ->title('No se puede anular este comprobante')
                            ->body('Verifique que no hayan pasado más de 7 días desde su emisión y que no haya sido anulado previamente.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($this->record->void($data['reason'])) {
                        \Filament\Notifications\Notification::make()
                            ->title('Comprobante anulado correctamente')
                            ->success()
                            ->send();
                        $this->redirect(InvoiceResource::getUrl('index'));
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Error al anular el comprobante')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
