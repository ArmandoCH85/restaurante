<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\CreditNote;
use App\Services\SunatService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreditNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'creditNotes';

    protected static ?string $title = 'Notas de Crédito';

    protected static ?string $modelLabel = 'Nota de Crédito';

    protected static ?string $pluralModelLabel = 'Notas de Crédito';

    protected static ?string $icon = 'heroicon-o-document-minus';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Nota de Crédito')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('serie')
                                    ->label('Serie')
                                    ->required()
                                    ->maxLength(4)
                                    ->disabled(),

                                Forms\Components\TextInput::make('numero')
                                    ->label('Número')
                                    ->required()
                                    ->numeric()
                                    ->disabled(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('motivo_codigo')
                                    ->label('Motivo')
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
                                    ->disabled(fn ($context) => $context === 'edit'),

                                Forms\Components\DatePicker::make('fecha_emision')
                                    ->label('Fecha de Emisión')
                                    ->required()
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('motivo_descripcion')
                            ->label('Descripción del Motivo')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->disabled(fn ($context) => $context === 'edit'),
                    ]),

                Forms\Components\Section::make('Importes')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('monto_operaciones_gravadas')
                                    ->label('Base Imponible')
                                    ->prefix('S/')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('monto_igv')
                                    ->label('IGV')
                                    ->prefix('S/')
                                    ->numeric()
                                    ->disabled(),

                                Forms\Components\TextInput::make('monto_total')
                                    ->label('Total')
                                    ->prefix('S/')
                                    ->numeric()
                                    ->disabled(),
                            ]),
                    ]),

                Forms\Components\Section::make('Estado SUNAT')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('sunat_status')
                                    ->label('Estado SUNAT')
                                    ->options([
                                        'PENDIENTE' => 'Pendiente',
                                        'ACEPTADO' => 'Aceptado',
                                        'RECHAZADO' => 'Rechazado',
                                        'ERROR' => 'Error',
                                    ])
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('sunat_response')
                            ->label('Respuesta de SUNAT')
                            ->rows(3)
                            ->disabled(),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('serie')
            ->columns([
                Tables\Columns\TextColumn::make('serie')
                    ->label('Serie')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('motivo_codigo')
                    ->label('Motivo')
                    ->formatStateUsing(fn ($state) => match($state) {
                        '01' => '01 - Anulación',
                        '02' => '02 - Error RUC',
                        '03' => '03 - Error descripción',
                        '04' => '04 - Descuento global',
                        '05' => '05 - Descuento ítem',
                        '06' => '06 - Devolución total',
                        '07' => '07 - Devolución ítem',
                        '08' => '08 - Bonificación',
                        '09' => '09 - Disminución valor',
                        '10' => '10 - Otros conceptos',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        '01' => 'danger',
                        '02', '03' => 'warning',
                        '04', '05', '08' => 'success',
                        '06', '07' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('monto_total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_emision')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'ACEPTADO' => 'success',
                        'RECHAZADO' => 'danger',
                        'ERROR' => 'warning',
                        'PENDIENTE' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('motivo_codigo')
                    ->label('Motivo')
                    ->options([
                        '01' => '01 - Anulación',
                        '02' => '02 - Error RUC',
                        '03' => '03 - Error descripción',
                        '04' => '04 - Descuento global',
                        '05' => '05 - Descuento ítem',
                        '06' => '06 - Devolución total',
                        '07' => '07 - Devolución ítem',
                        '08' => '08 - Bonificación',
                        '09' => '09 - Disminución valor',
                        '10' => '10 - Otros conceptos',
                    ]),

                Tables\Filters\SelectFilter::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'ACEPTADO' => 'Aceptado',
                        'RECHAZADO' => 'Rechazado',
                        'ERROR' => 'Error',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('crear_nota_credito')
                    ->label('Crear Nota de Crédito')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->visible(fn () => $this->getOwnerRecord()->canBeVoided())
                    ->form([
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
                            $invoice = $this->getOwnerRecord();
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

                                // Refrescar la tabla
                                $this->resetTable();
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
                    ->modalHeading('Crear Nota de Crédito')
                    ->modalDescription('Genere una nota de crédito para esta factura.')
                    ->modalSubmitActionLabel('Crear Nota de Crédito')
                    ->modalWidth('lg'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (CreditNote $record): string => 
                        route('filament.admin.resources.facturacion.notas-credito.view', $record)
                    ),

                Tables\Actions\Action::make('reenviar_sunat')
                    ->label('Reenviar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (CreditNote $record) => in_array($record->sunat_status, ['PENDIENTE', 'ERROR', 'RECHAZADO']))
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar a SUNAT')
                    ->modalDescription('¿Está seguro de que desea reenviar esta nota de crédito a SUNAT?')
                    ->action(function (CreditNote $record) {
                        try {
                            $sunatService = new SunatService();
                            $result = $sunatService->emitirNotaCredito(
                                $record->invoice,
                                $record->motivo_codigo,
                                $record->motivo_descripcion
                            );

                            $record->update([
                                'xml_path' => $result->xml_path ?? $record->xml_path,
                                'cdr_path' => $result->cdr_path ?? $record->cdr_path,
                                'sunat_status' => $result->sunat_status ?? 'PENDIENTE',
                                'sunat_response' => $result->sunat_response ?? null,
                            ]);

                            Notification::make()
                                ->title('Nota de crédito reenviada exitosamente')
                                ->success()
                                ->send();

                            $this->resetTable();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al reenviar a SUNAT')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('descargar_xml')
                    ->label('XML')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->visible(fn (CreditNote $record) => !empty($record->xml_path) && Storage::exists($record->xml_path))
                    ->action(function (CreditNote $record): StreamedResponse {
                        $filename = "NC-{$record->serie}-{$record->numero}.xml";
                        return Storage::download($record->xml_path, $filename);
                    }),

                Tables\Actions\Action::make('descargar_cdr')
                    ->label('CDR')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn (CreditNote $record) => !empty($record->cdr_path) && Storage::exists($record->cdr_path))
                    ->action(function (CreditNote $record): StreamedResponse {
                        $filename = "CDR-{$record->serie}-{$record->numero}.zip";
                        return Storage::download($record->cdr_path, $filename);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete_credit_note')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    protected function canCreate(): bool
    {
        return $this->getOwnerRecord()->canBeVoided();
    }

    protected function canEdit($record): bool
    {
        return $record->sunat_status === 'PENDIENTE';
    }

    protected function canDelete($record): bool
    {
        return $record->sunat_status === 'PENDIENTE';
    }
}