<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterExpenseResource\Pages;
use App\Models\CashRegisterExpense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashRegisterExpenseResource extends Resource
{
    protected static ?string $model = CashRegisterExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';

    protected static ?string $navigationGroup = '💰 Caja';

    protected static ?string $navigationLabel = 'Egresos';

    protected static ?string $modelLabel = 'Egreso';

    protected static ?string $pluralModelLabel = 'Egresos';

    protected static ?string $slug = 'egresos';

    protected static ?int $navigationSort = 15;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Egreso')
                    ->description('Registre los egresos o salidas de dinero de caja')
                    ->icon('heroicon-m-arrow-trending-down')
                    ->schema([
                        Forms\Components\Select::make('cash_register_id')
                            ->label('Caja Registradora')
                            ->relationship('cashRegister', 'id', function (Builder $query) {
                                return $query->where('is_active', true)
                                    ->orWhere('is_active', false)
                                    ->orderBy('opening_datetime', 'desc');
                            })
                            ->getOptionLabelUsing(function ($record) {
                                if (!$record) return '';
                                $status = $record->is_active ? 'Abierta' : 'Cerrada';
                                $date = $record->opening_datetime->format('d/m/Y H:i');
                                return "Caja #{$record->id} - {$status} ({$date})";
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Seleccione la caja a la que pertenece este egreso'),

                        Forms\Components\TextInput::make('concept')
                            ->label('Concepto')
                            ->placeholder('Ej: Pago a proveedor, Compra de insumos, Gastos varios...')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->prefix('S/')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->helperText('Ingrese el monto del egreso')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas/Observaciones')
                            ->placeholder('Detalles adicionales sobre el egreso...')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->prefix('#')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('cashRegister.id')
                    ->label('Caja')
                    ->formatStateUsing(fn ($record) => "Caja #{$record->cashRegister->id}")
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('concept')
                    ->label('Concepto')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getState();
                    }),
                    
                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->money('PEN')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('danger'),
                    
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->placeholder('Sin notas'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cash_register_id')
                    ->label('Caja')
                    ->relationship('cashRegister', 'id', function (Builder $query) {
                        return $query->orderBy('opening_datetime', 'desc');
                    })
                    ->getOptionLabelUsing(function ($record) {
                        if (!$record) return '';
                        $status = $record->is_active ? 'Abierta' : 'Cerrada';
                        $date = $record->opening_datetime->format('d/m/Y');
                        return "Caja #{$record->id} - {$status} ({$date})";
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\Section::make('Rango de Montos')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('min_amount')
                                            ->label('Monto Mínimo')
                                            ->prefix('S/')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01),
                                        Forms\Components\TextInput::make('max_amount')
                                            ->label('Monto Máximo')
                                            ->prefix('S/')
                                            ->numeric()
                                            ->minValue(0)
                                            ->step(0.01),
                                    ]),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'] ?? null,
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'] ?? null,
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min_amount'] ?? null) {
                            $indicators['min_amount'] = 'Mín: S/ ' . number_format($data['min_amount'], 2);
                        }
                        if ($data['max_amount'] ?? null) {
                            $indicators['max_amount'] = 'Máx: S/ ' . number_format($data['max_amount'], 2);
                        }
                        return $indicators;
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\Section::make('Rango de Fechas')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('date_from')
                                            ->label('Desde')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                        Forms\Components\DatePicker::make('date_to')
                                            ->label('Hasta')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ]),
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_to'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['date_from'] ?? null) {
                            $indicators['date_from'] = 'Desde: ' . \Carbon\Carbon::parse($data['date_from'])->format('d/m/Y');
                        }
                        if ($data['date_to'] ?? null) {
                            $indicators['date_to'] = 'Hasta: ' . \Carbon\Carbon::parse($data['date_to'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-m-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar este egreso?')
                    ->modalDescription('Esta acción no se puede deshacer.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar los egresos seleccionados?')
                        ->modalDescription('Esta acción no se puede deshacer.'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-arrow-trending-down')
            ->emptyStateHeading('📭 No hay egresos registrados')
            ->emptyStateDescription('Registre un nuevo egreso usando el botón de arriba.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Registrar Egreso')
                    ->icon('heroicon-m-plus')
                    ->color('danger'),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisterExpenses::route('/'),
            'create' => Pages\CreateCashRegisterExpense::route('/create'),
            'view' => Pages\ViewCashRegisterExpense::route('/{record}'),
            'edit' => Pages\EditCashRegisterExpense::route('/{record}/edit'),
        ];
    }
}