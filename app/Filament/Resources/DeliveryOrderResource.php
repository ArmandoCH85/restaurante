<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?string $navigationLabel = 'Delivery';

    protected static ?string $modelLabel = 'Pedido de Delivery';

    protected static ?string $pluralModelLabel = 'Pedidos de Delivery';

    protected static ?string $slug = 'ventas/delivery';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // OPTIMIZACIÓN: Agregar eager loading para evitar N+1 queries
        $query = parent::getEloquentQuery()
            ->with([
                'order.customer',
                'order.table',
                'order.orderDetails.product',
                'deliveryPerson'
            ]);

        // Obtener el usuario actual
        $user = \Illuminate\Support\Facades\Auth::user();

        // Verificar si el usuario tiene el rol "delivery" o "Delivery"
        if ($user && ($user->roles->where('name', 'delivery')->count() > 0 || $user->roles->where('name', 'Delivery')->count() > 0)) {
            // Buscar el empleado asociado al usuario
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();

            if ($employee) {
                // Filtrar para mostrar solo los pedidos asignados a este repartidor
                $query->where('delivery_person_id', $employee->id);

                // Registrar para depuración
                \Illuminate\Support\Facades\Log::info('Filtrando pedidos de delivery por repartidor', [
                    'user_id' => $user->id,
                    'employee_id' => $employee->id
                ]);
            }
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Pedido')
                    ->schema([
                        Forms\Components\TextInput::make('order_id')
                            ->label('Orden #')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'assigned' => 'Asignado',
                                'in_transit' => 'En tránsito',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('delivery_person_id')
                            ->label('Repartidor')
                            ->options(Employee::where('position', 'Delivery')->pluck('first_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nombre')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Apellido')
                                    ->required(),
                                Forms\Components\TextInput::make('document_number')
                                    ->label('Número de Documento')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->tel(),
                                Forms\Components\Hidden::make('position')
                                    ->default('Delivery'),
                            ]),
                    ])->columns(3),

                Forms\Components\Section::make('Dirección de Entrega')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_address')
                            ->label('Dirección')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('delivery_references')
                            ->label('Referencias')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Tiempos')
                    ->schema([
                        Forms\Components\DateTimePicker::make('estimated_delivery_time')
                            ->label('Tiempo Estimado de Entrega')
                            ->seconds(false)
                            ->timezone('America/Lima')
                            ->displayFormat('d/m/Y H:i'),

                        Forms\Components\DateTimePicker::make('actual_delivery_time')
                            ->label('Tiempo Real de Entrega')
                            ->seconds(false)
                            ->timezone('America/Lima')
                            ->displayFormat('d/m/Y H:i'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Orden #')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Dirección')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        // Usar un valor fijo de 30 en lugar de getLimit() que no existe
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('deliveryPerson.full_name')
                    ->label('Repartidor')
                    ->sortable()
                    ->searchable(),

                // SISTEMA DE SEMÁFORO: Columna personalizada con semáforo y badge
                Tables\Columns\ViewColumn::make('status')
                    ->label('Estado')
                    ->view('filament.tables.columns.delivery-status-with-traffic-light')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_delivery_time')
                    ->label('Tiempo Estimado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'assigned' => 'Asignado',
                        'in_transit' => 'En tránsito',
                        'delivered' => 'Entregado',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('delivery_person_id')
                    ->label('Repartidor')
                    ->relationship('deliveryPerson', 'first_name')
                    ->visible(function () {
                        // Ocultar el filtro si el usuario es un repartidor
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return !($user && ($user->roles->where('name', 'delivery')->count() > 0 || $user->roles->where('name', 'Delivery')->count() > 0));
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign_delivery')
                    ->label('Asignar Repartidor')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('delivery_person_id')
                            ->label('Repartidor')
                            ->options(Employee::where('position', 'Delivery')->pluck('first_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (DeliveryOrder $record, array $data): void {
                        $employee = Employee::find($data['delivery_person_id']);
                        if ($employee) {
                            $previousStatus = $record->status;
                            $record->assignDeliveryPerson($employee);

                            // Disparar evento de cambio de estado
                            event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                            // SISTEMA DE SEMÁFORO: Notificación de éxito
                            \Filament\Notifications\Notification::make()
                                ->title('Repartidor asignado')
                                ->body("Repartidor {$employee->full_name} asignado al pedido #{$record->order_id}")
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(function (DeliveryOrder $record) {
                        // Obtener el usuario actual
                        $user = \Illuminate\Support\Facades\Auth::user();

                        // Ocultar para usuarios con rol Delivery y mostrar solo para pedidos pendientes
                        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
                        return !$isDeliveryPerson && $record->isPending();
                    }),

                Tables\Actions\Action::make('mark_in_transit')
                    ->label('En Tránsito')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (DeliveryOrder $record): void {
                        $previousStatus = $record->status;
                        $record->markAsInTransit();

                        // Disparar evento de cambio de estado
                        event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                        // SISTEMA DE SEMÁFORO: Notificación de éxito
                        \Filament\Notifications\Notification::make()
                            ->title('Estado actualizado')
                            ->body("Pedido #{$record->order_id} marcado como En Tránsito")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(DeliveryOrder $record): bool => $record->isAssigned()),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Entregado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (DeliveryOrder $record): void {
                        $previousStatus = $record->status;
                        $record->markAsDelivered();

                        // Disparar evento de cambio de estado
                        event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                        // SISTEMA DE SEMÁFORO: Notificación de éxito
                        \Filament\Notifications\Notification::make()
                            ->title('Pedido entregado')
                            ->body("Pedido #{$record->order_id} marcado como Entregado")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(DeliveryOrder $record): bool => $record->isInTransit()),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo de Cancelación')
                            ->required(),
                    ])
                    ->action(function (DeliveryOrder $record, array $data): void {
                        $previousStatus = $record->status;
                        $record->cancel($data['reason'] ?? null);

                        // Disparar evento de cambio de estado
                        event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                        // SISTEMA DE SEMÁFORO: Notificación de éxito
                        \Filament\Notifications\Notification::make()
                            ->title('Pedido cancelado')
                            ->body("Pedido #{$record->order_id} ha sido cancelado")
                            ->warning()
                            ->send();
                    })
                    ->visible(fn(DeliveryOrder $record): bool => !$record->isDelivered() && !$record->isCancelled()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // Obtener el usuario actual
        $user = \Illuminate\Support\Facades\Auth::user();

        // Si el usuario tiene rol Delivery, usar la vista personalizada
        if ($user && $user->roles->where('name', 'Delivery')->count() > 0) {
            return [
                'index' => Pages\MyDeliveryOrders::route('/'),
            ];
        }

        // Para otros usuarios, usar las vistas estándar
        return [
            'index' => Pages\ListDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
