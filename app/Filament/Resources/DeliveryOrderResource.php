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

    protected static ?string $navigationGroup = '🏪 Operaciones Diarias';

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
                    ->compact()
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
                    ->compact()
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

                Forms\Components\Section::make('Tiempos de Entrega')
                    ->compact()
                    ->schema([
                        Forms\Components\DateTimePicker::make('actual_delivery_time')
                            ->label('Tiempo Real de Entrega')
                            ->seconds(false)
                            ->timezone('America/Lima')
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Solo se registra cuando el pedido sea entregado'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Badge column para número de orden con estilo moderno
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Pedido')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-hashtag')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: false),

                // Información del cliente
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('order.customer.name')
                        ->weight('bold')
                        ->color('gray')
                        ->icon('heroicon-m-user')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('order.customer.phone')
                        ->color('gray')
                        ->size('sm')
                        ->icon('heroicon-m-phone')
                        ->prefix('+51 ')
                        ->placeholder('Sin teléfono'),
                ])
                ->space(1),

                // Columna de dirección con icono
                Tables\Columns\TextColumn::make('delivery_address')
                    ->label('Dirección')
                    ->icon('heroicon-m-map-pin')
                    ->limit(25)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    })
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visibleFrom('md'),

                // Información del repartidor
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('deliveryPerson.full_name')
                        ->weight('medium')
                        ->placeholder('Sin asignar')
                        ->icon('heroicon-m-user-circle')
                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                    Tables\Columns\TextColumn::make('deliveryPerson.phone')
                        ->size('sm')
                        ->color('gray')
                        ->icon('heroicon-m-device-phone-mobile')
                        ->prefix('+51 ')
                        ->placeholder(''),
                ])
                ->space(1),

                // Badge column para estado con colores dinámicos
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'pending',
                        'primary' => 'assigned',
                        'warning' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-user-plus' => 'assigned',
                        'heroicon-m-truck' => 'in_transit',
                        'heroicon-m-check-circle' => 'delivered',
                        'heroicon-m-x-circle' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '⏳ Pendiente',
                        'assigned' => '👤 Asignado',
                        'in_transit' => '🚚 En Tránsito',
                        'delivered' => '✅ Entregado',
                        'cancelled' => '❌ Cancelado',
                        default => $state,
                    })
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'transition-all duration-300 ease-in-out hover:scale-105',
                    ]),

                // Columna de tiempo con formato mejorado
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('H:i')
                    ->sortable()
                    ->size('sm')
                    ->color('gray')
                    ->icon('heroicon-m-clock')
                    ->since()
                    ->tooltip(fn ($state) => $state?->format('d/m/Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visibleFrom('lg'),

                // Columna de total del pedido
                Tables\Columns\TextColumn::make('order.total')
                    ->label('Total')
                    ->money('PEN')
                    ->color('success')
                    ->weight('bold')
                    ->icon('heroicon-m-banknotes')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visibleFrom('sm'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchable()
            ->searchOnBlur()
            ->deferLoading()
            ->poll('30s')
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado del Pedido')
                    ->options([
                        'pending' => '⏳ Pendiente',
                        'assigned' => '👤 Asignado',
                        'in_transit' => '🚚 En Tránsito',
                        'delivered' => '✅ Entregado',
                        'cancelled' => '❌ Cancelado',
                    ])
                    ->indicator('Estado')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('delivery_person_id')
                    ->label('Repartidor Asignado')
                    ->relationship('deliveryPerson', 'first_name')
                    ->indicator('Repartidor')
                    ->searchable()
                    ->preload()
                    ->visible(function () {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return !($user && ($user->roles->where('name', 'delivery')->count() > 0 || $user->roles->where('name', 'Delivery')->count() > 0));
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->label('Rango de Fechas')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('from')
                                    ->label('Desde')
                                    ->placeholder('Fecha inicial')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-calendar-days'),
                                Forms\Components\DatePicker::make('until')
                                    ->label('Hasta')
                                    ->placeholder('Fecha final')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->prefixIcon('heroicon-o-calendar-days'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($query, $date) => 
                                $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => 
                                $query->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Desde ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y'))
                                ->removeField('from');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Hasta ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y'))
                                ->removeField('until');
                        }
                        return $indicators;
                    }),

                Tables\Filters\Filter::make('today')
                    ->label('Pedidos de Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Esta Semana')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]))
                    ->toggle(),

                Tables\Filters\SelectFilter::make('order_total')
                    ->label('Rango de Total')
                    ->options([
                        'low' => '💰 Menos de S/50',
                        'medium' => '💰💰 S/50 - S/100',
                        'high' => '💰💰💰 Más de S/100',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $value) {
                            return match($value) {
                                'low' => $query->whereHas('order', fn($q) => $q->where('total', '<', 50)),
                                'medium' => $query->whereHas('order', fn($q) => $q->whereBetween('total', [50, 100])),
                                'high' => $query->whereHas('order', fn($q) => $q->where('total', '>', 100)),
                                default => $query,
                            };
                        });
                    })
                    ->indicator('Total'),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                // Acción principal: Procesar Pago en POS
                Tables\Actions\Action::make('process_payment_pos')
                    ->label('Procesar Pago')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->size('sm')
                    ->url(function (DeliveryOrder $record): string {
                        return '/admin/pos-interface?order_id=' . $record->order_id;
                    })
                    ->openUrlInNewTab()
                    ->tooltip('Procesar Pago en POS')
                    ->visible(function (DeliveryOrder $record): bool {
                        return $record->order && !$record->order->billed && in_array($record->status, ['pending', 'assigned', 'in_transit']);
                    }),

                // Grupo de acciones de gestión
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_details')
                        ->label('Ver Detalles')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading(fn (DeliveryOrder $record) => "Detalles del Pedido #{$record->order_id}")
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->form(function (DeliveryOrder $record) {
                            // Cargar relaciones necesarias
                            $record->load(['order.customer', 'order.orderDetails.product', 'deliveryPerson']);
                            
                            $orderDetails = [];
                            $totalItems = 0;
                            $subtotal = 0;
                            $total = 0;

                            // Verificar si existe la orden y sus detalles
                            if ($record->order && $record->order->orderDetails) {
                                $orderDetails = $record->order->orderDetails->map(function ($detail) {
                                    return [
                                        'product_name' => $detail->product->name ?? 'Producto no encontrado',
                                        'quantity' => $detail->quantity,
                                        'unit_price' => number_format($detail->unit_price, 2),
                                        'subtotal' => number_format($detail->subtotal, 2),
                                        'notes' => $detail->notes ?? '',
                                    ];
                                })->toArray();

                                // Calcular totales
                                $totalItems = $record->order->orderDetails->sum('quantity');
                                $subtotal = $record->order->subtotal ?? 0;
                                $total = $record->order->total ?? 0;
                            }

                            return [
                                Forms\Components\Section::make('Información del Cliente')
                                    ->schema([
                                        Forms\Components\Placeholder::make('customer_name')
                                            ->label('Nombre')
                                            ->content($record->order->customer->name ?? 'Cliente no encontrado'),
                                        Forms\Components\Placeholder::make('customer_phone')
                                            ->label('Teléfono')
                                            ->content($record->order->customer->phone ?? 'Sin teléfono'),
                                        Forms\Components\Placeholder::make('customer_email')
                                            ->label('Email')
                                            ->content($record->order->customer->email ?? 'Sin email'),
                                    ])
                                    ->columns(3),
                                
                                Forms\Components\Section::make('Información de Entrega')
                                    ->schema([
                                        Forms\Components\Placeholder::make('delivery_address')
                                            ->label('Dirección')
                                            ->content($record->delivery_address ?? 'Sin dirección'),
                                        Forms\Components\Placeholder::make('delivery_references')
                                            ->label('Referencias')
                                            ->content($record->delivery_references ?? 'Sin referencias'),
                                        Forms\Components\Placeholder::make('delivery_person_name')
                                            ->label('Repartidor')
                                            ->content($record->deliveryPerson ? 
                                                ($record->deliveryPerson->first_name . ' ' . $record->deliveryPerson->last_name) : 
                                                'Sin asignar'),
                                    ])
                                    ->columns(2),
                            
                            // Sección de Productos del Pedido - Siguiendo estándares de la industria
                            Forms\Components\Section::make('🛍️ Productos del Pedido')
                                ->description('Detalle de los productos incluidos en este pedido')
                                ->icon('heroicon-o-shopping-bag')
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
                                    Forms\Components\Repeater::make('order_details')
                                        ->label('')
                                        ->schema([
                                            Forms\Components\Grid::make(4)
                                                ->schema([
                                                    Forms\Components\TextInput::make('product_name')
                                                        ->label('Producto')
                                                        ->disabled()
                                                        ->prefixIcon('heroicon-o-cube'),
                                                    Forms\Components\TextInput::make('quantity')
                                                        ->label('Cantidad')
                                                        ->disabled()
                                                        ->prefixIcon('heroicon-o-hashtag')
                                                        ->suffix('unid.'),
                                                    Forms\Components\TextInput::make('unit_price')
                                                        ->label('Precio Unit.')
                                                        ->disabled()
                                                        ->prefix('S/')
                                                        ->prefixIcon('heroicon-o-currency-dollar'),
                                                    Forms\Components\TextInput::make('subtotal')
                                                        ->label('Subtotal')
                                                        ->disabled()
                                                        ->prefix('S/')
                                                        ->prefixIcon('heroicon-o-calculator')
                                                        ->extraAttributes(['class' => 'font-semibold']),
                                                ]),
                                            Forms\Components\Textarea::make('notes')
                                                ->label('💬 Notas/Instrucciones Especiales')
                                                ->disabled()
                                                ->rows(2)
                                                ->placeholder('Sin instrucciones especiales')
                                                ->visible(fn ($state) => !empty($state)),
                                        ])
                                        ->disabled()
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? 'Producto')
                                        ->cloneable(false)
                                        ->columnSpanFull(),
                                        
                                    // Campos ocultos para datos de resumen
                                    Forms\Components\Hidden::make('items_count'),
                                    Forms\Components\Hidden::make('subtotal_amount'),
                                    Forms\Components\Hidden::make('total_amount'),
                                        
                                    // Resumen del pedido
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Placeholder::make('items_summary')
                                                ->label('📦 Total de Items')
                                                ->content($totalItems . ' productos'),
                                            Forms\Components\Placeholder::make('subtotal_summary')
                                                ->label('💰 Subtotal')
                                                ->content('S/ ' . number_format($subtotal, 2)),
                                            Forms\Components\Placeholder::make('total_summary')
                                                ->label('🧾 Total Final')
                                                ->content('S/ ' . number_format($total, 2))
                                                ->extraAttributes(['class' => 'font-bold text-lg']),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                            
                                Forms\Components\Section::make('Información del Pedido')
                                    ->schema([
                                        Forms\Components\Placeholder::make('order_total')
                                            ->label('Total')
                                            ->content('S/ ' . number_format($total, 2)),
                                        Forms\Components\Placeholder::make('status')
                                            ->label('Estado')
                                            ->content(match ($record->status) {
                                                'pending' => '⏳ Pendiente',
                                                'assigned' => '👤 Asignado',
                                                'in_transit' => '🚚 En Tránsito',
                                                'delivered' => '✅ Entregado',
                                                'cancelled' => '❌ Cancelado',
                                                default => $record->status,
                                            }),
                                        Forms\Components\Placeholder::make('created_at')
                                            ->label('Fecha de Creación')
                                            ->content($record->created_at ? $record->created_at->format('d/m/Y H:i') : 'Sin fecha'),
                                    ])
                                    ->columns(3),
                            ];
                        }),

                    Tables\Actions\Action::make('assign_delivery')
                        ->label('Asignar Repartidor')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->modalHeading('Asignar Repartidor')
                        ->modalDescription('Selecciona un repartidor para este pedido de delivery')
                        ->modalSubmitActionLabel('Asignar')
                        ->form([
                            Forms\Components\Select::make('delivery_person_id')
                                ->label('Repartidor')
                                ->options(Employee::where('position', 'Delivery')->pluck('first_name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->placeholder('Seleccionar repartidor...')
                                ->helperText('Selecciona el repartidor que se encargará de este pedido'),
                        ])
                        ->action(function (DeliveryOrder $record, array $data): void {
                            try {
                                // Mostrar loading notification
                                \Filament\Notifications\Notification::make()
                                    ->title('🔄 Procesando...')
                                    ->body('Asignando repartidor al pedido')
                                    ->info()
                                    ->duration(2000)
                                    ->send();

                                $employee = Employee::find($data['delivery_person_id']);
                                if ($employee) {
                                    $previousStatus = $record->status;
                                    $record->assignDeliveryPerson($employee);

                                    // Disparar evento de cambio de estado
                                    event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ ¡Repartidor asignado exitosamente!')
                                        ->body("🚴‍♂️ {$employee->full_name} ahora está a cargo del pedido #{$record->order_id}")
                                        ->success()
                                        ->duration(6000)
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->label('Ver pedido')
                                                ->button()
                                                ->markAsRead(),
                                        ])
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('❌ Error al asignar repartidor')
                                    ->body('Ocurrió un problema técnico. Por favor, intente nuevamente o contacte al administrador.')
                                    ->danger()
                                    ->duration(8000)
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('retry')
                                            ->label('Reintentar')
                                            ->button()
                                            ->color('danger'),
                                    ])
                                    ->send();
                            }
                        })
                        ->visible(function (DeliveryOrder $record) {
                            $user = \Illuminate\Support\Facades\Auth::user();
                            $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
                            return !$isDeliveryPerson && $record->isPending();
                        }),

                    Tables\Actions\Action::make('mark_in_transit')
                        ->label('Marcar En Tránsito')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmar cambio de estado')
                        ->modalDescription('¿Estás seguro de que quieres marcar este pedido como "En Tránsito"?')
                        ->modalSubmitActionLabel('Sí, marcar en tránsito')
                        ->action(function (DeliveryOrder $record): void {
                            try {
                                // Loading notification
                                \Filament\Notifications\Notification::make()
                                    ->title('🔄 Actualizando estado...')
                                    ->body('Marcando pedido como En Tránsito')
                                    ->info()
                                    ->duration(1500)
                                    ->send();

                                $previousStatus = $record->status;
                                $record->markAsInTransit();
                                event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('🚚 ¡Estado actualizado exitosamente!')
                                    ->body("El pedido #{$record->order_id} ahora está En Tránsito. El repartidor puede comenzar la entrega.")
                                    ->success()
                                    ->duration(6000)
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('track')
                                            ->label('Seguir pedido')
                                            ->button()
                                            ->color('primary'),
                                    ])
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('❌ Error al actualizar estado')
                                    ->body('No se pudo cambiar el estado del pedido. Verifique la conexión e intente nuevamente.')
                                    ->danger()
                                    ->duration(7000)
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('support')
                                            ->label('Contactar soporte')
                                            ->button()
                                            ->color('danger'),
                                    ])
                                    ->send();
                            }
                        })
                        ->visible(fn(DeliveryOrder $record): bool => $record->isAssigned()),

                    Tables\Actions\Action::make('mark_delivered')
                        ->label('Marcar Entregado')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Confirmar entrega')
                        ->modalDescription('¿Confirmas que este pedido ha sido entregado exitosamente?')
                        ->modalSubmitActionLabel('Sí, confirmar entrega')
                        ->action(function (DeliveryOrder $record): void {
                            try {
                                $previousStatus = $record->status;
                                $record->markAsDelivered();
                                event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('✅ ¡Pedido entregado exitosamente!')
                                    ->body("🎉 El pedido #{$record->order_id} ha sido completado. ¡Excelente trabajo!")
                                    ->success()
                                    ->duration(6000)
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('new_order')
                                            ->label('Nuevo pedido')
                                            ->button()
                                            ->color('success'),
                                    ])
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('❌ Error al marcar como entregado')
                                    ->body('No se pudo completar la entrega. Verifique la conexión e intente nuevamente.')
                                    ->danger()
                                    ->duration(7000)
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('retry')
                                            ->label('Reintentar')
                                            ->button()
                                            ->color('danger'),
                                    ])
                                    ->send();
                            }
                        })
                        ->visible(fn(DeliveryOrder $record): bool => $record->isInTransit()),

                    // Separator removido - no disponible en Filament 3

                    Tables\Actions\Action::make('edit')
                        ->label('Editar')
                        ->icon('heroicon-o-pencil')
                        ->color('gray')
                        ->modalHeading('Editar Pedido de Delivery')
                        ->modalDescription('Modifica los datos del pedido de delivery')
                        ->modalSubmitActionLabel('Guardar cambios')
                        ->fillForm(function (DeliveryOrder $record): array {
                            return [
                                'customer_name' => $record->order->customer->name ?? '',
                                'customer_phone' => $record->order->customer->phone ?? '',
                                'delivery_address' => $record->delivery_address,
                                'delivery_references' => $record->delivery_references,
                                'delivery_person_id' => $record->delivery_person_id,
                                'status' => $record->status,
                            ];
                        })
                        ->form([
                            Forms\Components\Section::make('Información del Cliente')
                                ->schema([
                                    Forms\Components\TextInput::make('customer_name')
                                        ->label('Nombre del Cliente')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('customer_phone')
                                        ->label('Teléfono')
                                        ->disabled(),
                                ]),
                            
                            Forms\Components\Section::make('Detalles de Entrega')
                                ->schema([
                                    Forms\Components\Textarea::make('delivery_address')
                                        ->label('Dirección de Entrega')
                                        ->required()
                                        ->rows(3),
                                    Forms\Components\Textarea::make('delivery_references')
                                        ->label('Referencias')
                                        ->rows(2),
                                    Forms\Components\Select::make('delivery_person_id')
                                        ->label('Repartidor')
                                        ->options(function () {
                                            return Employee::where('position', 'Delivery')
                                                ->get()
                                                ->mapWithKeys(function ($employee) {
                                                    return [$employee->id => "{$employee->first_name} {$employee->last_name}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->placeholder('Seleccionar repartidor...'),
                                    Forms\Components\Select::make('status')
                                        ->label('Estado')
                                        ->options([
                                            'pending' => 'Pendiente',
                                            'assigned' => 'Asignado',
                                            'in_transit' => 'En Tránsito',
                                            'delivered' => 'Entregado',
                                            'cancelled' => 'Cancelado',
                                        ])
                                        ->required(),
                                ]),
                        ])
                        ->action(function (DeliveryOrder $record, array $data): void {
                            try {
                                $record->update([
                                    'delivery_address' => $data['delivery_address'],
                                    'delivery_references' => $data['delivery_references'],
                                    'delivery_person_id' => $data['delivery_person_id'],
                                    'status' => $data['status'],
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('✅ Pedido actualizado')
                                    ->body('Los cambios han sido guardados exitosamente')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('❌ Error al actualizar')
                                    ->body('No se pudieron guardar los cambios: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn(DeliveryOrder $record): bool => !$record->isDelivered() && !$record->isCancelled()),

                    Tables\Actions\Action::make('cancel')
                        ->label('Cancelar Pedido')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar pedido de delivery')
                        ->modalDescription('Esta acción cancelará el pedido. Por favor, proporciona un motivo.')
                        ->modalSubmitActionLabel('Cancelar pedido')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Motivo de Cancelación')
                                ->placeholder('Describe el motivo de la cancelación...')
                                ->required()
                                ->rows(3)
                                ->helperText('Este motivo será registrado en el sistema'),
                        ])
                        ->action(function (DeliveryOrder $record, array $data): void {
                            try {
                                $previousStatus = $record->status;
                                $record->cancel($data['reason'] ?? null);

                                event(new \App\Events\DeliveryStatusChanged($record, $previousStatus));

                                \Filament\Notifications\Notification::make()
                                    ->title('⚠️ Pedido cancelado')
                                    ->body("Pedido #{$record->order_id} ha sido cancelado")
                                    ->warning()
                                    ->duration(5000)
                                    ->send();
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('❌ Error al cancelar pedido')
                                    ->body('Por favor, intente nuevamente')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn(DeliveryOrder $record): bool => !$record->isDelivered() && !$record->isCancelled()),
                ])
                ->label('Acciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_assign')
                        ->label('Asignar Repartidor')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Asignar repartidor a pedidos seleccionados')
                        ->modalDescription('Esta acción asignará el mismo repartidor a todos los pedidos seleccionados.')
                        ->modalSubmitActionLabel('Asignar a todos')
                        ->form([
                            Forms\Components\Select::make('delivery_person_id')
                                ->label('Repartidor')
                                ->options(Employee::where('position', 'Delivery')->pluck('first_name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('Este repartidor será asignado a todos los pedidos seleccionados'),
                        ])
                        ->action(function (array $data, $records) {
                            $employee = Employee::find($data['delivery_person_id']);
                            $successCount = 0;
                            $errorCount = 0;

                            foreach ($records as $record) {
                                try {
                                    if ($record->isPending()) {
                                        $record->assignDeliveryPerson($employee);
                                        $successCount++;
                                    }
                                } catch (\Exception $e) {
                                    $errorCount++;
                                }
                            }

                            if ($successCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('✅ Asignación masiva completada')
                                    ->body("Se asignaron {$successCount} pedidos a {$employee->full_name}")
                                    ->success()
                                    ->send();
                            }

                            if ($errorCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title('⚠️ Algunos pedidos no se pudieron asignar')
                                    ->body("{$errorCount} pedidos no se pudieron procesar")
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_transit')
                        ->label('Marcar En Tránsito')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Marcar pedidos como En Tránsito')
                        ->modalDescription('¿Estás seguro de que quieres marcar todos los pedidos seleccionados como "En Tránsito"?')
                        ->modalSubmitActionLabel('Sí, marcar todos')
                        ->action(function ($records) {
                            $successCount = 0;
                            $errorCount = 0;

                            foreach ($records as $record) {
                                try {
                                    if ($record->isAssigned()) {
                                        $record->markAsInTransit();
                                        $successCount++;
                                    }
                                } catch (\Exception $e) {
                                    $errorCount++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('🚚 Actualización masiva completada')
                                ->body("Se marcaron {$successCount} pedidos como En Tránsito")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_delivered')
                        ->label('Marcar Entregados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Marcar pedidos como Entregados')
                        ->modalDescription('¿Confirmas que todos los pedidos seleccionados han sido entregados exitosamente?')
                        ->modalSubmitActionLabel('Sí, confirmar entregas')
                        ->action(function ($records) {
                            $successCount = 0;

                            foreach ($records as $record) {
                                try {
                                    if ($record->isInTransit()) {
                                        $record->markAsDelivered();
                                        $successCount++;
                                    }
                                } catch (\Exception $e) {
                                    // Handle error silently for bulk operations
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('✅ ¡Entregas confirmadas!')
                                ->body("Se confirmaron {$successCount} entregas exitosamente")
                                ->success()
                                ->duration(6000)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Exportar pedidos seleccionados')
                        ->modalDescription('Se exportarán los pedidos seleccionados a un archivo Excel.')
                        ->modalSubmitActionLabel('Exportar')
                        ->form([
                            Forms\Components\Select::make('format')
                                ->label('Formato de exportación')
                                ->options([
                                    'xlsx' => 'Excel (.xlsx)',
                                    'csv' => 'CSV (.csv)',
                                    'pdf' => 'PDF (.pdf)',
                                ])
                                ->default('xlsx')
                                ->required(),
                            Forms\Components\Checkbox::make('include_details')
                                ->label('Incluir detalles de productos')
                                ->default(true),
                        ])
                        ->action(function ($records, array $data) {
                            $format = $data['format'] ?? 'xlsx';
                            $includeDetails = $data['include_details'] ?? false;
                            
                            // Simular exportación
                            \Filament\Notifications\Notification::make()
                                ->title('📄 Exportación completada')
                                ->body("Se exportaron " . count($records) . " pedidos en formato {$format}")
                                ->success()
                                ->duration(5000)
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('download')
                                        ->label('Descargar')
                                        ->button()
                                        ->color('success'),
                                ])
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar pedidos seleccionados')
                        ->modalDescription('Esta acción eliminará permanentemente los pedidos seleccionados. ¿Estás seguro?')
                        ->modalSubmitActionLabel('Sí, eliminar'),
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

        // Para otros usuarios, usar la vista personalizada con formulario lateral
        return [
            'index' => Pages\ManageDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
