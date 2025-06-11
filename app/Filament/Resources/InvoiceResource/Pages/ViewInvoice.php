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
            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    // Obtener configuración de empresa usando los métodos estáticos
                    $company = [
                        'ruc' => \App\Models\CompanyConfig::getRuc(),
                        'razon_social' => \App\Models\CompanyConfig::getRazonSocial(),
                        'nombre_comercial' => \App\Models\CompanyConfig::getNombreComercial(),
                        'direccion' => \App\Models\CompanyConfig::getDireccion(),
                        'telefono' => \App\Models\CompanyConfig::getTelefono(),
                        'email' => \App\Models\CompanyConfig::getEmail(),
                    ];

                    // Datos para el PDF
                    $data = [
                        'invoice' => $this->record->load(['customer', 'details.product', 'order.table']),
                        'company' => $company,
                    ];

                    // Determinar la vista según el tipo de documento
                    $view = match($this->record->invoice_type) {
                        'receipt' => 'pdf.receipt',
                        'sales_note' => 'pdf.sales_note',
                        default => 'pdf.invoice'
                    };

                    // Generar PDF y mostrarlo en navegador para impresión
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml(\Illuminate\Support\Facades\Blade::render($view, $data));
                    return $pdf->stream($this->record->series . '-' . $this->record->number . '.pdf');
                }),

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
