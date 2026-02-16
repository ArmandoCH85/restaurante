<?php

namespace App\Filament\Widgets;

use App\Models\CreditNote;
use App\Helpers\SunatServiceHelper;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class LatestCreditNotesWidget extends BaseWidget
{
    protected static ?string $heading = 'Últimas Notas de Crédito';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $pollingInterval = '30s';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CreditNote::query()
                    ->with(['invoice', 'createdBy'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('serie')
                    ->label('Serie')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('invoice.serie')
                    ->label('Factura')
                    ->formatStateUsing(fn ($record) => $record->invoice ? 
                        "{$record->invoice->serie}-{$record->invoice->correlativo}" : 'N/A')
                    ->searchable(['invoice.serie', 'invoice.correlativo']),
                    
                TextColumn::make('motivo_descripcion')
                    ->label('Motivo')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),
                    
                BadgeColumn::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->colors([
                        'success' => 'ACEPTADO',
                        'danger' => 'RECHAZADO',
                        'warning' => 'PENDIENTE',
                        'gray' => fn ($state): bool => is_null($state),
                    ])
                    ->formatStateUsing(fn ($state) => $state ?? 'SIN ENVIAR'),
                    
                TextColumn::make('fecha_envio')
                    ->label('Fecha Envío')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No enviado'),
                    
                TextColumn::make('createdBy.name')
                    ->label('Creado por')
                    ->placeholder('Sistema'),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->url(fn (CreditNote $record): string => 
                        route('filament.admin.resources.credit-notes.view', $record)
                    ),
                    
                Action::make('download_xml')
                    ->label('XML')
                    ->icon('heroicon-o-document-text')
                    ->action(function (CreditNote $record) {
                        if ($record->xml_path && Storage::exists($record->xml_path)) {
                            return Storage::download($record->xml_path);
                        }
                        
                        $this->notify('danger', 'Archivo XML no encontrado');
                    })
                    ->visible(fn (CreditNote $record): bool => 
                        $record->xml_path && Storage::exists($record->xml_path)
                    ),
                    
                Action::make('download_cdr')
                    ->label('CDR')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (CreditNote $record) {
                        if ($record->cdr_path && Storage::exists($record->cdr_path)) {
                            return Storage::download($record->cdr_path);
                        }
                        
                        $this->notify('danger', 'Archivo CDR no encontrado');
                    })
                    ->visible(fn (CreditNote $record): bool => 
                        $record->cdr_path && Storage::exists($record->cdr_path)
                    ),
                    
                Action::make('resend')
                    ->label('Reenviar')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (CreditNote $record) {
                        try {
                            $sunatService = SunatServiceHelper::createIfNotTesting();
                            if ($sunatService === null) {
                                Notification::make()
                                    ->title('Modo de pruebas')
                                    ->body('El servicio SUNAT no está disponible en modo de pruebas.')
                                    ->warning()
                                    ->send();

                                return;
                            }
                            $result = $sunatService->emitirNotaCredito($record->invoice);
                            
                            if ($result['success']) {
                                $this->notify('success', 'Nota de crédito reenviada exitosamente');
                            } else {
                                $this->notify('danger', 'Error al reenviar: ' . $result['error']);
                            }
                        } catch (\Exception $e) {
                            $this->notify('danger', 'Error: ' . $e->getMessage());
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (CreditNote $record): bool => 
                        $record->sunat_status !== 'ACEPTADO'
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
    
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }
}