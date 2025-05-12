
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Models\CashRegister;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?string $navigationLabel = 'Apertura y Cierre de Caja';

    protected static ?string $modelLabel = 'Operación de Caja';

    protected static ?string $pluralModelLabel = 'Operaciones de Caja';

    protected static ?string $slug = 'operaciones-caja';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Apertura')
                    ->description('Datos de apertura de caja')
                    ->schema([
                        Forms\Components\TextInput::make('opening_amount')
                            ->label('Monto Inicial')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->prefix('S/')
                            ->columnSpan(2),
                        Forms\Components\DateTimePicker::make('opening_datetime')
                            ->label('Fecha y Hora de Apertura')
                            ->required()
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('opened_by_name')
                            ->label('Abierto por')
                            ->formatStateUsing(fn ($record) => $record->openedBy->name ?? '')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        Forms\Components\Hidden::make('opened_by')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && !$record->is_active),

                Forms\Components\Section::make('Información de Apertura')
                    ->description('Datos de apertura de caja')
                    ->schema([
                        Forms\Components\TextInput::make('opening_amount')
                            ->label('Monto Inicial')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('S/')
                            ->placeholder('0.00')
                            ->helperText('Ingrese el monto inicial con el que abre la caja')
                            ->rules(['required', 'numeric', 'min:0'])
                            ->validationMessages([
                                'required' => 'El monto inicial es obligatorio',
                                'numeric' => 'El monto inicial debe ser un número',
                                'min' => 'El monto inicial debe ser mayor o igual a cero',
                            ])
                            ->columnSpan(2),
                        Forms\Components\DateTimePicker::make('opening_datetime')
                            ->label('Fecha y Hora de Apertura')
                            ->required()
                            ->default(now())
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('opened_by_name')
                            ->label('Abierto por')
                            ->default(auth()->user()->name)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        Forms\Components\Hidden::make('opened_by')
                            ->default(auth()->id())
                            ->required(),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observaciones')
                            ->placeholder('Observaciones sobre la apertura de caja')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => !$record || $record->is_active),

                Forms\Components\Section::make('Conteo de Efectivo')
                    ->description('Ingrese la cantidad de billetes y monedas para el cierre de caja')
                    ->schema([
                        Forms\Components\Fieldset::make('Billetes')
                            ->schema([
                                Forms\Components\TextInput::make('bill_10')
                                    ->label('S/10')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('bill_20')
                                    ->label('S/20')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('bill_50')
                                    ->label('S/50')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('bill_100')
                                    ->label('S/100')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('bill_200')
                                    ->label('S/200')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                            ])
                            ->columns(5),

                        Forms\Components\Fieldset::make('Monedas')
                            ->schema([
                                Forms\Components\TextInput::make('coin_010')
                                    ->label('S/0.10')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('coin_020')
                                    ->label('S/0.20')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('coin_050')
                                    ->label('S/0.50')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('coin_1')
                                    ->label('S/1')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('coin_2')
                                    ->label('S/2')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                                Forms\Components\TextInput::make('coin_5')
                                    ->label('S/5')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required(),
                            ])
                            ->columns(6),

                        Forms\Components\Textarea::make('closing_observations')
                            ->label('Observaciones de Cierre')
                            ->placeholder('Observaciones sobre el cierre de caja')
                            ->columnSpan('full'),
                    ])
                    ->visible(fn ($record) => $record && $record->is_active),

                Forms\Components\Section::make('Resumen de Ventas')
                    ->description('Resumen de ventas por método de pago')
                    ->schema(function () {
                        $user = auth()->user();
                        $isSupervisor = $user->hasAnyRole(['admin', 'super_admin', 'manager']);

                        if ($isSupervisor) {
                            return [
                                Forms\Components\TextInput::make('cash_sales')
                                    ->label('Ventas en Efectivo')
                                    ->disabled()
                                    ->prefix('S/')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('card_sales')
                                    ->label('Ventas con Tarjeta')
                                    ->disabled()
                                    ->prefix('S/')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('other_sales')
                                    ->label('Otras Ventas')
                                    ->disabled()
                                    ->prefix('S/')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('total_sales')
                                    ->label('Ventas Totales')
                                    ->disabled()
                                    ->prefix('S/')
                                    ->columnSpan(1),
                            ];
                        } else {
                            return [
                                Forms\Components\Placeholder::make('sales_info')
                                    ->label('Información de Ventas')
                                    ->content('Esta información solo es visible para supervisores')
                                    ->columnSpan('full'),
                                Forms\Components\Placeholder::make('blind_closing_info')
                                    ->label('Cierre a Ciegas')
                                    ->content('Por favor, realice el conteo de efectivo sin conocer los montos esperados')
                                    ->columnSpan('full'),
                            ];
                        }
                    })
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->is_active),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_datetime')
                    ->label('Fecha de Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('openedBy.name')
                    ->label('Abierto por')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_amount')
                    ->label('Monto Inicial')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Ventas Totales')
                    ->state(function ($record) {
                        $user = auth()->user();
                        if ($user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
                            return $record->total_sales;
                        } else {
                            return 'Información reservada';
                        }
                    })
                    ->money(function () {
                        $user = auth()->user();
                        return $user->hasAnyRole(['admin', 'super_admin', 'manager']) ? 'PEN' : null;
                    })
                    ->sortable()
                    ->tooltip(function () {
                        $user = auth()->user();
                        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
                            return 'Esta información solo es visible para supervisores';
                        }
                        return null;
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'Abierta',
                        'danger' => 'Cerrada',
                    ]),
                Tables\Columns\BadgeColumn::make('reconciliationStatus')
                    ->label('Estado Aprobación')
                    ->colors([
                        'success' => 'Aprobada',
                        'warning' => 'Pendiente de reconciliación',
                        'danger' => 'Rechazada',
                        'info' => 'Pendiente de cierre',
                    ])
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']))
                    ->tooltip(function ($record) {
                        if (!$record->is_active && $record->approval_notes) {
                            return $record->is_approved
                                ? 'Notas: ' . $record->approval_notes
                                : 'Motivo del rechazo: ' . $record->approval_notes;
                        }
                        return 'Estado de aprobación del cierre de caja';
                    }),
                Tables\Columns\TextColumn::make('closing_datetime')
                    ->label('Fecha de Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('No cerrada'),
                Tables\Columns\TextColumn::make('closedBy.name')
                    ->label('Cerrado por')
                    ->placeholder('No cerrada')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado de Apertura')
                    ->options([
                        1 => 'Abierta',
                        0 => 'Cerrada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null),

                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Estado de Aprobación')
                    ->options([
                        1 => 'Aprobada',
                        0 => 'Pendiente/Rechazada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager'])),

                Tables\Filters\Filter::make('opening_datetime')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde ' . $data['desde'];
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta ' . $data['hasta'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('closing_datetime')
                    ->form([
                        Forms\Components\DatePicker::make('desde_cierre')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta_cierre')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde_cierre'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_datetime', '>=', $date),
                            )
                            ->when(
                                $data['hasta_cierre'],
                                fn (Builder $query, $date): Builder => $query->whereDate('closing_datetime', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['desde_cierre'] ?? null) {
                            $indicators['desde_cierre'] = 'Cierre desde ' . $data['desde_cierre'];
                        }

                        if ($data['hasta_cierre'] ?? null) {
                            $indicators['hasta_cierre'] = 'Cierre hasta ' . $data['hasta_cierre'];
                        }

                        return $indicators;
                    })
                    ->label('Fecha de Cierre'),

                Tables\Filters\SelectFilter::make('opened_by')
                    ->label('Abierto por')
                    ->relationship('openedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('closed_by')
                    ->label('Cerrado por')
                    ->relationship('closedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('difference')
                    ->form([
                        Forms\Components\TextInput::make('min_difference')
                            ->label('Diferencia mínima')
                            ->numeric()
                            ->placeholder('-100.00'),
                        Forms\Components\TextInput::make('max_difference')
                            ->label('Diferencia máxima')
                            ->numeric()
                            ->placeholder('100.00'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_difference'] !== null,
                                fn (Builder $query, $min): Builder => $query->where('difference', '>=', $data['min_difference']),
                            )
                            ->when(
                                $data['max_difference'] !== null,
                                fn (Builder $query, $max): Builder => $query->where('difference', '<=', $data['max_difference']),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (isset($data['min_difference'])) {
                            $indicators['min_difference'] = 'Diferencia mín: S/ ' . $data['min_difference'];
                        }

                        if (isset($data['max_difference'])) {
                            $indicators['max_difference'] = 'Diferencia máx: S/ ' . $data['max_difference'];
                        }

                        return $indicators;
                    })
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager'])),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (CashRegister $record) => $record->is_active),
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar Cierre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (CashRegister $record) {
                        $user = auth()->user();
                        return !$record->is_active && !$record->is_approved && $user->hasAnyRole(['admin', 'super_admin', 'manager']);
                    })
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Notas de Aprobación')
                            ->placeholder('Observaciones sobre la aprobación del cierre')
                            ->columnSpan('full'),
                    ])
                    ->action(function (CashRegister $record, array $data) {
                        try {
                            // Usar el nuevo método de reconciliación
                            $record->reconcile(
                                true, // Aprobar
                                $data['approval_notes']
                            );

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Cierre reconciliado y aprobado')
                                ->body('El cierre de caja ha sido reconciliado y aprobado correctamente.')
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error al reconciliar')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Cierre de Caja')
                    ->modalDescription('¿Estás seguro de que deseas aprobar este cierre de caja? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, aprobar cierre'),
                Tables\Actions\Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (CashRegister $record) => url("/admin/print-cash-register/{$record->id}"))
                    ->openUrlInNewTab()
                    ->visible(function (CashRegister $record) {
                        // Solo permitir imprimir cajas cerradas o a usuarios con roles específicos
                        return !$record->is_active || auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']);
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rechazar Cierre')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(function (CashRegister $record) {
                        $user = auth()->user();
                        return !$record->is_active && !$record->is_approved && $user->hasAnyRole(['admin', 'super_admin', 'manager']);
                    })
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Motivo del Rechazo')
                            ->placeholder('Indique el motivo por el que rechaza este cierre')
                            ->required()
                            ->columnSpan('full'),
                    ])
                    ->action(function (CashRegister $record, array $data) {
                        try {
                            // Usar el nuevo método de reconciliación con aprobación = false
                            $record->reconcile(
                                false, // Rechazar
                                $data['approval_notes']
                            );

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Cierre rechazado')
                                ->body('El cierre de caja ha sido marcado como rechazado.')
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error al rechazar')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Cierre de Caja')
                    ->modalDescription('¿Estás seguro de que deseas rechazar este cierre de caja? Deberá indicar el motivo del rechazo.')
                    ->modalSubmitActionLabel('Sí, rechazar cierre'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin'])),
                ]),
            ])
            ->defaultSort('opening_datetime', 'desc')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('No hay cajas registradas')
            ->emptyStateDescription('Registra tu primera caja para comenzar a gestionar tus ventas.')
            ->emptyStateActions([
                Tables\Actions\Action::make('crear')
                    ->label('Abrir Caja')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
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
            'index' => Pages\ListCashRegisters::route('/'),
            'create' => Pages\CreateCashRegister::route('/create'),
            'edit' => Pages\EditCashRegister::route('/{record}/edit'),
            'view' => Pages\ViewCashRegister::route('/{record}'),
        ];
    }
}
