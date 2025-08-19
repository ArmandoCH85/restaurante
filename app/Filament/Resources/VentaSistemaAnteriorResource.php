<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaSistemaAnteriorResource\Pages;
use App\Models\VentaSistemaAnterior;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VentaSistemaAnteriorResource extends Resource
{
    protected static ?string $model = VentaSistemaAnterior::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Ventas Sistema Anterior';

    protected static ?string $modelLabel = 'Venta Sistema Anterior';

    protected static ?string $pluralModelLabel = 'Ventas Sistema Anterior';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('🆔 ID')
                    ->sortable()
                    ->searchable()
                    ->size('sm')
                    ->weight('medium')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('fecha_venta')
                    ->label('📅 Fecha Venta')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->copyable()
                    ->tooltip('Hacer clic para copiar'),
                    
                Tables\Columns\TextColumn::make('cliente')
                    ->label('👤 Cliente')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->cliente)
                    ->weight('medium'),
                    
                Tables\Columns\BadgeColumn::make('documento')
                    ->label('📄 Documento')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'primary' => 'Factura',
                        'success' => 'Boleta',
                        'warning' => 'Nota de Venta',
                        'secondary' => fn ($state) => !in_array($state, ['Factura', 'Boleta', 'Nota de Venta']),
                    ]),
                    
                Tables\Columns\BadgeColumn::make('canal_venta')
                    ->label('📱 Canal')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'success' => 'DELIVERY',
                        'primary' => 'MOSTRADOR',
                        'info' => fn ($state) => str_contains($state, 'Mesa'),
                        'secondary' => fn ($state) => !in_array($state, ['DELIVERY', 'MOSTRADOR']) && !str_contains($state, 'Mesa'),
                    ])
                    ->toggleable(),
                    
                Tables\Columns\BadgeColumn::make('tipo_pago')
                    ->label('💳 Pago')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'success' => 'EFECTIVO',
                        'primary' => 'TARJETA',
                        'warning' => ['YAPE', 'PLIN'],
                        'info' => 'TRANSFERENCIA',
                        'secondary' => fn ($state) => !in_array($state, ['EFECTIVO', 'TARJETA', 'YAPE', 'PLIN', 'TRANSFERENCIA']),
                    ])
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('💰 Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),
                    
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('⚡ Estado')
                    ->sortable()
                    ->searchable()
                    ->colors([
                        'success' => 'APROBADO',
                        'danger' => 'ANULADO',
                        'warning' => fn ($state) => !in_array($state, ['APROBADO', 'ANULADO']),
                    ])
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('caja')
                    ->label('🏪 Caja')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('📥 Importado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('🔄 Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->updated_at->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 📅 Filtro por rango de fechas
                Tables\Filters\Filter::make('fecha_venta')
                    ->label('📅 Rango de Fechas')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->placeholder('Fecha inicial')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta')
                            ->placeholder('Fecha final')
                            ->native(false),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        $query = $query->where(function ($subQuery) use ($data) {
                            if ($data['desde'] && !$data['hasta']) {
                                // Solo fecha desde
                                $formattedDate = \Carbon\Carbon::parse($data['desde'])->format('d-m-Y');
                                $subQuery->where('fecha_venta', 'LIKE', $formattedDate . '%');
                            } elseif (!$data['desde'] && $data['hasta']) {
                                // Solo fecha hasta
                                $formattedDate = \Carbon\Carbon::parse($data['hasta'])->format('d-m-Y');
                                $subQuery->where('fecha_venta', 'LIKE', $formattedDate . '%');
                            } elseif ($data['desde'] && $data['hasta']) {
                                // Rango de fechas
                                $fechaDesde = \Carbon\Carbon::parse($data['desde']);
                                $fechaHasta = \Carbon\Carbon::parse($data['hasta']);
                                
                                // Si es la misma fecha
                                if ($fechaDesde->format('d-m-Y') === $fechaHasta->format('d-m-Y')) {
                                    $formattedDate = $fechaDesde->format('d-m-Y');
                                    $subQuery->where('fecha_venta', 'LIKE', $formattedDate . '%');
                                } else {
                                    // Rango de fechas diferentes
                                    $subQuery->where(function ($rangeQuery) use ($fechaDesde, $fechaHasta) {
                                        $current = $fechaDesde->copy();
                                        while ($current->lte($fechaHasta)) {
                                            $rangeQuery->orWhere('fecha_venta', 'LIKE', $current->format('d-m-Y') . '%');
                                            $current->addDay();
                                        }
                                    });
                                }
                            }
                        });
                        
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators[] = '📅 Desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators[] = '📅 Hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                
                // 📱 Filtro por canal de venta
                Tables\Filters\SelectFilter::make('canal_venta')
                    ->label('📱 Canal de Venta')
                    ->placeholder('Todos los canales')
                    ->options([
                        'DELIVERY' => '🚚 Delivery',
                        'MOSTRADOR' => '🏪 Mostrador',
                    ])
                    ->attribute('canal_venta'),
                
                // 💳 Filtro por tipo de pago
                Tables\Filters\SelectFilter::make('tipo_pago')
                    ->label('💳 Tipo de Pago')
                    ->placeholder('Todos los métodos')
                    ->options([
                        'EFECTIVO' => '💵 Efectivo',
                        'TARJETA' => '💳 Tarjeta',
                        'YAPE' => '📱 Yape',
                        'PLIN' => '📲 Plin',
                        'TRANSFERENCIA' => '🏦 Transferencia',
                        'DIDI' => '🚗 DiDi',
                        'PEDIDOS YA' => '🛵 Pedidos Ya',
                    ])
                    ->attribute('tipo_pago'),
                
                // 📄 Filtro por documento
                Tables\Filters\SelectFilter::make('documento')
                    ->label('📄 Tipo de Documento')
                    ->placeholder('Todos los documentos')
                    ->options(function () {
                        try {
                            return VentaSistemaAnterior::query()
                                ->whereNotNull('documento')
                                ->distinct()
                                ->pluck('documento', 'documento')
                                ->mapWithKeys(function ($item) {
                                    $icon = match($item) {
                                        'Boleta' => '🧾',
                                        'Factura' => '📄',
                                        'Nota de Venta' => '📝',
                                        default => '📋'
                                    };
                                    return [$item => $icon . ' ' . $item];
                                })
                                ->toArray();
                        } catch (\Exception $e) {
                            return [
                                'Boleta' => '🧾 Boleta',
                                'Factura' => '📄 Factura',
                                'Nota de Venta' => '📝 Nota de Venta',
                            ];
                        }
                    })
                    ->attribute('documento'),
                
                // 💰 Filtro por estado
                Tables\Filters\SelectFilter::make('estado')
                    ->label('⚡ Estado')
                    ->placeholder('Todos los estados')
                    ->options([
                        'APROBADO' => '✅ Aprobado',
                        'ANULADO' => '❌ Anulado',
                    ])
                    ->attribute('estado'),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ])->label('Acciones masivas'),
            ])
            ->emptyStateHeading('🔍 No hay ventas registradas')
            ->emptyStateDescription('No se encontraron ventas del sistema anterior con los filtros aplicados.')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass');
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVentaSistemaAnteriores::route('/'),
            'view' => Pages\ViewVentaSistemaAnterior::route('/{record}'),
        ];
    }
}