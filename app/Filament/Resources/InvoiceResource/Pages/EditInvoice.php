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
            Actions\Action::make('fix_stuck_invoice')
                ->label('Corregir Envío')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('info')
                ->visible(fn () => $this->record && in_array($this->record->sunat_status, ['ENVIANDO', 'ERROR']) && in_array($this->record->invoice_type, ['invoice','receipt']))
                ->form([
                    \Filament\Forms\Components\Select::make('method')
                        ->label('Método de Reenvío')
                        ->options([
                            'qps' => 'QPS (Recomendado)',
                            'sunat' => 'SUNAT Directo'
                        ])
                        ->default('qps')
                        ->required()
                        ->helperText('QPS es más estable y maneja mejor los errores de timeout'),
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Motivo de la Corrección')
                        ->placeholder('Ej: Timeout de 30 segundos, error de conexión, etc.')
                        ->rows(2)
                        ->maxLength(255)
                ])
                ->modalHeading('Corregir Comprobante Atascado')
                ->modalDescription('Este comprobante quedó en estado de envío. Se reseteará el estado y se reenviará.')
                ->modalSubmitActionLabel('Corregir y Reenviar')
                ->action(function (array $data) {
                    try {
                        $method = $data['method'];
                        $reason = $data['reason'] ?? 'Corrección manual desde interfaz';
                        
                        // Log de la acción
                        \Illuminate\Support\Facades\Log::info('Corrección manual de factura atascada', [
                            'invoice_id' => $this->record->id,
                            'series_number' => $this->record->series . '-' . $this->record->number,
                            'old_status' => $this->record->sunat_status,
                            'method' => $method,
                            'reason' => $reason,
                            'user_id' => auth()->id()
                        ]);
                        
                        // Resetear estado
                        $this->record->update([
                            'sunat_status' => 'PENDIENTE',
                            'sunat_code' => null,
                            'sunat_description' => null,
                            'sunat_response' => null
                        ]);
                        
                        // Reenviar según método seleccionado
                        if ($method === 'qps') {
                            $qpsService = new \App\Services\QpsService();
                            $result = $qpsService->sendInvoiceViaQps($this->record);
                        } else {
                            $sunatService = new \App\Services\SunatService();
                            $result = $sunatService->emitirFactura($this->record->id);
                        }
                        
                        if ($result['success'] ?? false) {
                            Notification::make()
                                ->title('✅ Comprobante Corregido')
                                ->body("Reenviado exitosamente vía {$method}")
                                ->success()
                                ->duration(5000)
                                ->send();
                            $this->refreshRecord();
                        } else {
                            Notification::make()
                                ->title('❌ Error en Corrección')
                                ->body($result['message'] ?? 'Error desconocido al reenviar')
                                ->danger()
                                ->duration(8000)
                                ->send();
                        }
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('🚨 Error Inesperado')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
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
