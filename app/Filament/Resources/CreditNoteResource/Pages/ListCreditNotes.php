<?php

namespace App\Filament\Resources\CreditNoteResource\Pages;

use App\Filament\Resources\CreditNoteResource;
use App\Models\Invoice;
use App\Services\SunatService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ListCreditNotes extends ListRecords
{
    protected static string $resource = CreditNoteResource::class;

    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('crear_desde_factura')
                ->label('Crear desde Factura')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('invoice_id')
                        ->label('Seleccionar Factura')
                        ->options(function () {
                            return Invoice::whereIn('sunat_status', ['ACEPTADO', 'OBSERVADO'])
                                ->whereDoesntHave('creditNotes', function (Builder $query) {
                                    $query->where('motivo_codigo', '01'); // No tiene nota de anulación
                                })
                                ->with(['customer'])
                                ->get()
                                ->mapWithKeys(function ($invoice) {
                                    return [
                                        $invoice->id => "{$invoice->series}-{$invoice->number} - {$invoice->customer->business_name} (S/ {$invoice->total})"
                                    ];
                                });
                        })
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('motivo_codigo')
                        ->label('Motivo de la Nota de Crédito')
                        ->options([
                            '01' => '01 - Anulación de la operación',
                            '02' => '02 - Anulación por error en el RUC',
                            '03' => '03 - Corrección por error en la descripción',
                            '04' => '04 - Descuento global',
                            '05' => '05 - Descuento por ítem',
                            '06' => '06 - Devolución total',
                            '07' => '07 - Devolución por ítem',
                            '08' => '08 - Bonificación',
                            '09' => '09 - Disminución en el valor',
                            '10' => '10 - Otros conceptos',
                        ])
                        ->required()
                        ->default('01'),

                    Forms\Components\Textarea::make('motivo_descripcion')
                        ->label('Descripción del Motivo')
                        ->required()
                        ->maxLength(500)
                        ->default('Anulación de la operación')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        $invoice = Invoice::findOrFail($data['invoice_id']);
                        $sunatService = new SunatService();
                        
                        $result = $sunatService->emitirNotaCredito(
                            $invoice,
                            $data['motivo_codigo'],
                            $data['motivo_descripcion']
                        );

                        if ($result['success']) {
                            $creditNote = $result['credit_note'];
                            Notification::make()
                                ->title('Nota de crédito creada exitosamente')
                                ->body("Serie: {$creditNote->serie}-{$creditNote->numero}")
                                ->success()
                                ->send();

                            return redirect()->route('filament.admin.resources.facturacion.notas-credito.view', $creditNote);
                        } else {
                            throw new \Exception($result['error']);
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al crear la nota de crédito')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Crear Nota de Crédito desde Factura')
                ->modalDescription('Seleccione una factura y el motivo para generar la nota de crédito.')
                ->modalSubmitActionLabel('Crear Nota de Crédito')
                ->modalWidth('lg'),

            Actions\Action::make('exportar_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Implementar exportación a Excel
                    Notification::make()
                        ->title('Funcionalidad en desarrollo')
                        ->body('La exportación a Excel estará disponible próximamente.')
                        ->info()
                        ->send();
                }),

            Actions\Action::make('reporte_sunat')
                ->label('Reporte SUNAT')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->action(function () {
                    // Implementar reporte para SUNAT
                    Notification::make()
                        ->title('Funcionalidad en desarrollo')
                        ->body('El reporte SUNAT estará disponible próximamente.')
                        ->info()
                        ->send();
                }),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['invoice.customer']);
    }

    public function getTitle(): string
    {
        return 'Notas de Crédito';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets de estadísticas
        ];
    }
}