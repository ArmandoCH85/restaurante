<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Notifications\Notification;
use App\Services\SunatService;
use App\Helpers\PdfHelper;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_to_sunat')
                ->label('Enviar a SUNAT')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => $this->record && in_array($this->record->invoice_type, ['invoice','receipt']) && in_array($this->record->sunat_status, [null, 'PENDIENTE']))
                ->requiresConfirmation()
                ->modalHeading('Enviar Comprobante a SUNAT')
                ->modalDescription(fn () => "¿Está seguro de enviar el comprobante {$this->record->series}-{$this->record->number} a SUNAT?")
                ->modalSubmitActionLabel('Enviar')
                ->action(function () {
                    try {
                        $service = new SunatService();
                        $result = $service->emitirFactura($this->record->id);
                        if ($result['success']) {
                            Notification::make()->title('Comprobante enviado correctamente')->success()->send();
                            $this->refreshRecord();
                        } else {
                            Notification::make()->title('Error al enviar')->body($result['message'])->danger()->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()->title('Error inesperado')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\Action::make('resend_to_sunat')
                ->label('Reenviar SUNAT')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record && $this->record->sunat_status === 'RECHAZADO' && in_array($this->record->invoice_type, ['invoice','receipt']))
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        // Usar QPS exclusivamente
                        $qpsService = new \App\Services\QpsService();
                        $result = $qpsService->sendInvoiceViaQps($this->record);
                        if ($result['success']) {
                            Notification::make()->title('Comprobante reenviado vía QPS')->success()->send();
                            $this->refreshRecord();
                        } else {
                            Notification::make()->title('Error al reenviar')->body($result['message'])->danger()->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()->title('Error inesperado')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\Action::make('void')
                ->label('Anular')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record && $this->record->canBeVoided())
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
                ->modalSubmitActionLabel('Anular')
                ->action(function (array $data): void {
                    if (!$data['confirm']) {
                        Notification::make()->title('Debe confirmar la anulación')->danger()->send();
                        return;
                    }
                    if (!$this->record->canBeVoided()) {
                        Notification::make()->title('No se puede anular este comprobante')->danger()->send();
                        return;
                    }
                    if ($this->record->void($data['reason'])) {
                        Notification::make()->title('Comprobante anulado')->success()->send();
                        $this->redirect(InvoiceResource::getUrl('view', ['record' => $this->record]));
                    } else {
                        Notification::make()->title('Error al anular')->danger()->send();
                    }
                }),
            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('filament.admin.invoices.print-ticket', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Evitar que se alteren importes manualmente: forzar a mantener los originales
        if ($this->record) {
            $immutable = ['taxable_amount','tax','total','series','number','issue_date','invoice_type'];
            foreach ($immutable as $field) {
                if (isset($this->record->{$field})) {
                    $data[$field] = $this->record->{$field};
                }
            }
        }
        return $data;
    }
}
