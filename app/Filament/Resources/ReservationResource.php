<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use App\Models\Table as TableModel;
use App\Models\Customer;
use App\Services\ReservationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Carbon;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = '📅 Reservas y Eventos';

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $modelLabel = 'Reserva';

    protected static ?string $pluralModelLabel = 'Reservas';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Información del Cliente')
                            ->description('Datos del cliente que realiza la reserva')
                            ->icon('heroicon-o-user')
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable(['name', 'document_number', 'phone'])
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options(Customer::DOCUMENT_TYPES)
                                            ->required(),
                                        Forms\Components\TextInput::make('document_number')
                                            ->label('Número de Documento')
                                            ->required()
                                            ->maxLength(15),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                        return $action
                                            ->modalHeading('Crear nuevo cliente')
                                            ->modalWidth('md');
                                    }),
                            ]),

                        Forms\Components\Section::make('Detalles de la Reserva')
                            ->description('Información sobre la reserva')
                            ->icon('heroicon-o-calendar')
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\DatePicker::make('reservation_date')
                                    ->label('Fecha')
                                    ->required()
                                    ->minDate(now())
                                    ->default(now())
                                    ->closeOnDateSelection()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('table_id', null);
                                    }),

                                Forms\Components\TimePicker::make('reservation_time')
                                    ->label('Hora')
                                    ->required()
                                    ->seconds(false)
                                    ->default('19:00')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('table_id', null);
                                    }),

                                Forms\Components\TextInput::make('guests_count')
                                    ->label('Número de Comensales')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(2)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('table_id', null);
                                    }),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'pending' => 'Pendiente',
                                        'confirmed' => 'Confirmada',
                                        'cancelled' => 'Cancelada',
                                        'completed' => 'Completada',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->live(),
                            ]),
                    ]),

                Forms\Components\Section::make('Mesa')
                    ->description('Seleccione una mesa disponible para la reserva')
                    ->icon('heroicon-o-table-cells')
                    ->schema([
                        Forms\Components\Select::make('table_id')
                            ->label('Mesa')
                            ->options(function (callable $get, $record, $livewire) {
                                $date = $get('reservation_date');
                                $time = $get('reservation_time');
                                $guestsCount = $get('guests_count');

                                if (!$date || !$time || !$guestsCount) {
                                    return [];
                                }

                                $reservationService = new ReservationService();
                                $availableTables = $reservationService->getAvailableTables(
                                    $date,
                                    $time,
                                    $guestsCount
                                );

                                // Si estamos editando, incluir la mesa actual
                                if ($record && $record->table_id) {
                                    $currentTable = TableModel::find($record->table_id);
                                    if ($currentTable && !in_array($currentTable->id, array_column($availableTables, 'id'))) {
                                        $availableTables[] = $currentTable;
                                    }
                                }

                                // Si se proporcionó un ID de mesa en la URL, incluirla
                                if (!$record && isset($livewire->tableId)) {
                                    $selectedTable = TableModel::find($livewire->tableId);
                                    if ($selectedTable && !in_array($selectedTable->id, array_column($availableTables, 'id'))) {
                                        $availableTables[] = $selectedTable;
                                    }
                                }

                                $options = [];
                                foreach ($availableTables as $table) {
                                    $options[$table->id] = "Mesa {$table->number} - {$table->capacity} personas - " . ucfirst($table->location);
                                }

                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->default(function ($livewire) {
                                return $livewire->tableId;
                            })
                            ->required(fn (callable $get) => $get('status') === 'confirmed')
                            ->disabled(fn (callable $get) => in_array($get('status'), ['cancelled', 'completed']))
                            ->helperText('Solo se muestran mesas disponibles para la fecha, hora y número de comensales seleccionados'),
                    ]),

                Forms\Components\Section::make('Observaciones')
                    ->description('Información adicional sobre la reserva')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        Forms\Components\Textarea::make('special_requests')
                            ->label('Solicitudes Especiales')
                            ->placeholder('Ej: Mesa cerca de la ventana, celebración de cumpleaños, etc.')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservation_time')
                    ->label('Hora')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('table.number')
                    ->label('Mesa')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? "Mesa {$state}" : '-'),

                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Comensales')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                        'completed' => 'Completada',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('special_requests')
                    ->label('Solicitudes Especiales')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                        'completed' => 'Completada',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('reservation_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reservation_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reservation_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('reservation_date', Carbon::today())),

                Tables\Filters\Filter::make('tomorrow')
                    ->label('Mañana')
                    ->query(fn (Builder $query): Builder => $query->whereDate('reservation_date', Carbon::tomorrow())),

                Tables\Filters\Filter::make('this_week')
                    ->label('Esta semana')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('reservation_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Reservation $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $reservationService = new ReservationService();
                        $reservationService->updateReservation($record, ['status' => 'confirmed']);

                        Notification::make()
                            ->title('Reserva confirmada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Reservation $record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $reservationService = new ReservationService();
                        $reservationService->cancelReservation($record);

                        Notification::make()
                            ->title('Reserva cancelada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Reservation $record) => $record->status === 'confirmed')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $reservationService = new ReservationService();
                        $reservationService->completeReservation($record);

                        Notification::make()
                            ->title('Reserva completada')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirmBulk')
                        ->label('Confirmar Seleccionadas')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $reservationService = new ReservationService();
                            $count = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    $reservationService->updateReservation($record, ['status' => 'confirmed']);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("{$count} reservas confirmadas")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('cancelBulk')
                        ->label('Cancelar Seleccionadas')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $reservationService = new ReservationService();
                            $count = 0;

                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'confirmed'])) {
                                    $reservationService->cancelReservation($record);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("{$count} reservas canceladas")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('reservation_date', 'asc');
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
            'view' => Pages\ViewReservation::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'confirmed'])
            ->whereDate('reservation_date', '>=', now())
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
