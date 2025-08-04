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

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = '📄 Facturación y Ventas';

    // Mostrar en el menú de navegación
    protected static bool $shouldRegisterNavigation = true;

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
                    ->icon('heroicon-m-information-circle')
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
                    ->icon('heroicon-m-calculator')
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
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Section::make('Billetes')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
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
                                            ]),
                                    ]),
                                Forms\Components\Section::make('Monedas')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
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
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Textarea::make('closing_observations')
                            ->label('Observaciones de Cierre')
                            ->placeholder('Observaciones sobre el cierre de caja')
                            ->columnSpan('full'),
                    ])
                    ->visible(fn ($record) => $record && $record->is_active),

                Forms\Components\Section::make('Resumen de Ventas')
                    ->description('Resumen de ventas por método de pago')
                    ->icon('heroicon-m-chart-bar')
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
                // ID con diseño mejorado
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->size('sm')
                    ->weight('bold'),

                // Información temporal agrupada
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('opening_datetime')
                        ->label('📅 Apertura')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-m-play-circle')
                        ->iconColor('success')
                        ->weight('medium')
                        ->size('sm'),

                    Tables\Columns\TextColumn::make('closing_datetime')
                        ->label('🔒 Cierre')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('En curso')
                        ->icon(fn ($state) => $state ? 'heroicon-m-stop-circle' : 'heroicon-m-clock')
                        ->iconColor(fn ($state) => $state ? 'danger' : 'warning')
                        ->color(fn ($state) => $state ? 'success' : 'warning')
                        ->size('sm'),
                ])->space(1),

                // Responsables agrupados
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('openedBy.name')
                        ->label('👤 Abierto por')
                        ->searchable()
                        ->icon('heroicon-m-user')
                        ->iconColor('primary')
                        ->weight('medium')
                        ->size('sm'),

                    Tables\Columns\TextColumn::make('closedBy.name')
                        ->label('👤 Cerrado por')
                        ->placeholder('En curso')
                        ->icon('heroicon-m-check-circle')
                        ->iconColor('success')
                        ->color(fn ($state) => $state ? 'success' : 'gray')
                        ->size('sm'),
                ])->space(1),

                // Estado principal con diseño destacado
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => $record->is_active ? 'ABIERTA' : 'CERRADA')
                    ->colors([
                        'success' => 'ABIERTA',
                        'danger' => 'CERRADA',
                    ])
                    ->icons([
                        'heroicon-m-lock-open' => 'ABIERTA',
                        'heroicon-m-lock-closed' => 'CERRADA',
                    ])
                    ->size('lg'),

                // Montos (solo para supervisores)
                Tables\Columns\TextColumn::make('opening_amount')
                    ->label('💰 Monto Inicial')
                    ->money('PEN')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager'])),

                // Estado de aprobación mejorado
                Tables\Columns\BadgeColumn::make('reconciliationStatus')
                    ->label('Aprobación')
                    ->getStateUsing(function ($record) {
                        if ($record->is_active) {
                            return 'Pendiente';
                        }
                        if ($record->is_approved) {
                            return 'Aprobada';
                        }
                        if ($record->approval_notes && !$record->is_approved) {
                            return 'Rechazada';
                        }
                        return 'Sin revisar';
                    })
                    ->colors([
                        'success' => 'Aprobada',
                        'warning' => 'Pendiente',
                        'danger' => 'Rechazada',
                        'gray' => 'Sin revisar',
                    ])
                    ->icons([
                        'heroicon-m-check-circle' => 'Aprobada',
                        'heroicon-m-clock' => 'Sin revisar',
                        'heroicon-m-x-circle' => 'Rechazada',
                        'heroicon-m-exclamation-triangle' => 'Pendiente',
                    ])
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']))
                    ->tooltip(function ($record) {
                        if (!$record->is_active && $record->approval_notes) {
                            return $record->is_approved
                                ? '✅ Notas: ' . $record->approval_notes
                                : '❌ Motivo: ' . $record->approval_notes;
                        }
                        return 'Estado de aprobación del cierre';
                    })
                    ->size('lg'),
            ])
            ->filters([
                // Filtro de estado mejorado con iconos
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('🔄 Estado de Operación')
                    ->options([
                        1 => '🟢 Abierta',
                        0 => '🔴 Cerrada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->multiple(false),

                // Filtro de aprobación mejorado
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('✅ Estado de Aprobación')
                    ->options([
                        1 => '✅ Aprobada',
                        0 => '⏳ Pendiente/Rechazada',
                    ])
                    ->placeholder('Todos los estados')
                    ->default(null)
                    ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager'])),

                // Filtro de responsable
                Tables\Filters\SelectFilter::make('opened_by')
                    ->label('👤 Responsable')
                    ->relationship('openedBy', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los responsables'),

                // Filtro de fecha mejorado con presets
                Tables\Filters\Filter::make('opening_datetime')
                    ->label('📅 Período')
                    ->form([
                        Forms\Components\Section::make('Rango de Fechas')
                            ->description('Seleccione el período de operaciones')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('desde')
                                            ->label('Desde')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                        Forms\Components\DatePicker::make('hasta')
                                            ->label('Hasta')
                                            ->native(false)
                                            ->displayFormat('d/m/Y'),
                                    ]),

                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('hoy')
                                                ->label('Hoy')
                                                ->color('primary')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', today()->format('Y-m-d'));
                                                    $set('hasta', today()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('ayer')
                                                ->label('Ayer')
                                                ->color('gray')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', yesterday()->format('Y-m-d'));
                                                    $set('hasta', yesterday()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('semana')
                                                ->label('Esta Semana')
                                                ->color('info')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', now()->startOfWeek()->format('Y-m-d'));
                                                    $set('hasta', now()->endOfWeek()->format('Y-m-d'));
                                                }),
                                            Forms\Components\Actions\Action::make('mes')
                                                ->label('Este Mes')
                                                ->color('success')
                                                ->size('sm')
                                                ->action(function (Forms\Set $set) {
                                                    $set('desde', now()->startOfMonth()->format('Y-m-d'));
                                                    $set('hasta', now()->endOfMonth()->format('Y-m-d'));
                                                }),
                                        ]),
                                    ]),
                            ]),
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
                            $indicators['desde'] = '📅 Desde: ' . \Carbon\Carbon::parse($data['desde'])->format('d/m/Y');
                        }

                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = '📅 Hasta: ' . \Carbon\Carbon::parse($data['hasta'])->format('d/m/Y');
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
            ->defaultSort('id', 'desc')
            ->actions([
                // Ver detalles (acción principal simplificada)
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->color('primary')
                    ->button(),

                // Cerrar caja (solo cajas activas)
                Tables\Actions\EditAction::make()
                    ->label('Cerrar')
                    ->icon('heroicon-m-lock-closed')
                    ->color('warning')
                    ->button()
                    ->visible(fn (CashRegister $record) => $record->is_active),

                // Imprimir (todas las cajas)
                Tables\Actions\Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->button()
                    ->url(fn (CashRegister $record) => url("/admin/print-cash-register/{$record->id}"))
                    ->openUrlInNewTab(),

                // Aprobar (solo supervisores y cajas cerradas)
                Tables\Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->action(function (CashRegister $record) {
                        try {
                            $record->reconcile(true, 'Aprobado desde lista');
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('✅ Caja aprobada')
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('❌ Error al aprobar')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(function (CashRegister $record) {
                        $user = auth()->user();
                        return !$record->is_active && !$record->is_approved &&
                               $user->hasAnyRole(['admin', 'super_admin', 'manager']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'super_admin'])),
                ]),
            ])
            ->headerActions([
                // Acción principal: Abrir nueva caja (solo cuando no hay caja abierta)
                Tables\Actions\CreateAction::make()
                    ->label('🏦 Abrir Nueva Caja')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->size('lg')
                    ->button()
                    ->visible(function () {
                        return !CashRegister::getOpenRegister();
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['opened_by'] = auth()->id();
                        $data['opening_datetime'] = now();
                        $data['is_active'] = true;
                        return $data;
                    })
                    ->successNotificationTitle('✅ Caja abierta correctamente')
                    ->after(function () {
                        redirect()->to('/admin/pos-interface');
                    }),

                // Reconciliación visible (principio KISS)
                Tables\Actions\Action::make('reconcile_all')
                    ->label('⚖️ Reconciliar Pendientes')
                    ->icon('heroicon-m-scale')
                    ->color('warning')
                    ->button()
                    ->visible(function () {
                        $user = auth()->user();
                        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
                            return false;
                        }
                        // Solo mostrar si hay cajas pendientes de reconciliar
                        return CashRegister::where('is_active', false)
                            ->where('is_approved', false)
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reconciliar Cajas Pendientes')
                    ->modalDescription('¿Desea marcar todas las cajas cerradas como reconciliadas?')
                    ->action(function () {
                        $count = CashRegister::where('is_active', false)
                            ->where('is_approved', false)
                            ->update(['is_approved' => true, 'approval_notes' => 'Reconciliación masiva']);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("✅ {$count} cajas reconciliadas")
                            ->send();
                    }),

                // Exportar (acción secundaria simple)
                Tables\Actions\Action::make('export_today')
                    ->label('📊 Exportar Hoy')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('info')
                    ->button()
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->info()
                            ->title('📊 Exportando...')
                            ->body('Se está generando el reporte del día.')
                            ->send();
                    }),
            ])
            ->defaultSort('opening_datetime', 'desc')
            ->emptyStateIcon('heroicon-o-calculator')
            ->emptyStateHeading('🏦 No hay cajas registradas')
            ->emptyStateDescription('Use el botón "Abrir Nueva Caja" en la parte superior para comenzar.');
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
