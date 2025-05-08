<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Models\CashRegister;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashRegisterResource extends Resource
{
    // Los scripts se cargan directamente en el layout
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Facturación';

    protected static ?string $navigationLabel = 'Cierre de Caja';

    protected static ?string $modelLabel = 'Cierre de Caja';

    protected static ?string $pluralModelLabel = 'Cierres de Caja';

    protected static ?string $slug = 'operaciones/cierres-caja';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Apertura')
                    ->schema([
                        Forms\Components\TextInput::make('opening_amount')
                            ->label('Monto Inicial')
                            ->prefix('S/')
                            ->numeric()
                            ->default(0)
                            ->disabled(fn ($record) => $record !== null)
                            ->required(),

                        Forms\Components\DateTimePicker::make('opening_datetime')
                            ->label('Fecha/Hora de Apertura')
                            ->disabled()
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('opened_by')
                            ->label('Abierto por')
                            ->relationship('openedBy', 'name')
                            ->default(Auth::id())
                            ->disabled()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Información de Cierre')
                    ->schema([
                        Forms\Components\TextInput::make('cash_sales')
                            ->label('Ventas en Efectivo')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('card_sales')
                            ->label('Ventas con Tarjeta')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('other_sales')
                            ->label('Otras Ventas')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('total_sales')
                            ->label('Total Ventas')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('expected_amount')
                            ->label('Efectivo Esperado')
                            ->prefix('S/')
                            ->disabled()
                            ->helperText('Monto inicial + Ventas en efectivo')
                            ->numeric(),

                        Forms\Components\TextInput::make('actual_amount')
                            ->label('Efectivo Real')
                            ->prefix('S/')
                            ->required(fn ($record) => $record && $record->is_active === CashRegister::STATUS_OPEN)
                            ->disabled(fn ($record) => $record && $record->is_active === CashRegister::STATUS_CLOSED)
                            ->numeric(),

                        Forms\Components\TextInput::make('difference')
                            ->label('Diferencia')
                            ->prefix('S/')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\Textarea::make('observations')
                            ->label('Notas')
                            ->placeholder('Observaciones sobre el cierre de caja')
                            ->disabled(fn ($record) => $record && $record->is_active === CashRegister::STATUS_CLOSED)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('closing_datetime')
                            ->label('Fecha/Hora de Cierre')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
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
                    ->label('Fecha Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('openedBy.name')
                    ->label('Abierto por')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Ventas')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cash_sales')
                    ->label('Ventas Efectivo')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('card_sales')
                    ->label('Ventas Tarjeta')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('difference')
                    ->label('Diferencia')
                    ->money('PEN')
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success'))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('is_active')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state): string => $state ? 'Abierto' : 'Cerrado')
                    ->colors([
                        'success' => fn ($state): bool => $state,
                        'gray' => fn ($state): bool => !$state,
                    ]),

                Tables\Columns\TextColumn::make('closing_datetime')
                    ->label('Fecha Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Abierto',
                        '0' => 'Cerrado',
                    ]),
                Tables\Filters\Filter::make('opening_datetime')
                    ->form([
                        Forms\Components\DatePicker::make('opened_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('opened_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['opened_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '>=', $date),
                            )
                            ->when(
                                $data['opened_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('opening_datetime', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (CashRegister $record): bool => $record->is_active === CashRegister::STATUS_OPEN),
                Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (CashRegister $record): string => route('admin.cash-register.print', $record))
                    ->openUrlInNewTab(false)
                    ->extraAttributes([
                        'onclick' => "event.preventDefault(); window.showCashRegisterModal(this.href); return false;"
                    ]),
                Action::make('close')
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (CashRegister $record): bool => $record->is_active === CashRegister::STATUS_OPEN)
                    ->form([
                        Forms\Components\TextInput::make('actual_amount')
                            ->label('Efectivo Real en Caja')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('S/'),
                        Forms\Components\Textarea::make('observations')
                            ->label('Notas')
                            ->placeholder('Observaciones sobre el cierre de caja'),
                        Forms\Components\Checkbox::make('confirm')
                            ->label('Confirmo que la información es correcta y deseo cerrar la caja')
                            ->required()
                            ->default(false),
                    ])
                    ->action(function (CashRegister $record, array $data): void {
                        if (!$data['confirm']) {
                            Notification::make()
                                ->title('Debe confirmar el cierre de caja')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Calcular totales
                        $payments = Payment::where('cash_register_id', $record->id)
                            ->select(
                                'payment_method',
                                DB::raw('SUM(amount) as total')
                            )
                            ->groupBy('payment_method')
                            ->get()
                            ->keyBy('payment_method');

                        $cashSales = $payments->get('cash', (object)['total' => 0])->total;
                        $cardSales = $payments->get('credit_card', (object)['total' => 0])->total +
                                    $payments->get('debit_card', (object)['total' => 0])->total;
                        $otherSales = $payments->get('bank_transfer', (object)['total' => 0])->total +
                                     $payments->get('digital_wallet', (object)['total' => 0])->total;
                        $totalSales = $cashSales + $cardSales + $otherSales;
                        $expectedCash = $record->opening_amount + $cashSales;
                        $actualCash = $data['actual_amount'];
                        $difference = $actualCash - $expectedCash;

                        // Actualizar el registro
                        $closeData = [
                            'closed_by' => Auth::id(),
                            'cash_sales' => $cashSales,
                            'card_sales' => $cardSales,
                            'other_sales' => $otherSales,
                            'total_sales' => $totalSales,
                            'expected_amount' => $expectedCash,
                            'actual_amount' => $actualCash,
                            'difference' => $difference,
                            'notes' => $data['observations'],
                        ];

                        if ($record->close($closeData)) {
                            Notification::make()
                                ->title('Caja cerrada correctamente')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error al cerrar la caja')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Sin acciones masivas
            ])
            ->defaultSort('opening_datetime', 'desc');
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', CashRegister::STATUS_OPEN)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
