<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Table as TableModel;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = '🏪 Operaciones Diarias';

    protected static ?string $navigationLabel = 'Órdenes de Venta Directa';

    protected static ?string $modelLabel = 'Orden';

    protected static ?string $pluralModelLabel = 'Órdenes';

    protected static ?string $slug = 'venta-directa/ordenes';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Orden')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('service_type')
                                    ->label('Tipo de Servicio')
                                    ->options([
                                        'dine_in' => 'Mesa',
                                        'takeout' => 'Para Llevar',
                                        'delivery' => 'Delivery',
                                        'drive_thru' => 'Drive Thru',
                                    ])
                                    ->required()
                                    ->default('dine_in')
                                    ->live(),

                                Forms\Components\Select::make('table_id')
                                    ->label('Mesa')
                                    ->relationship('table', 'number')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Forms\Get $get) => $get('service_type') === 'dine_in'),

                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono'),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email(),
                                    ]),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Empleado')
                                    ->relationship('employee', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                                    ->searchable(['first_name', 'last_name'])
                                    ->default(Auth::id())
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'open' => 'Abierta',
                                        'in_preparation' => 'En Preparación',
                                        'ready' => 'Lista',
                                        'delivered' => 'Entregada',
                                        'completed' => 'Completada',
                                        'cancelled' => 'Cancelada',
                                    ])
                                    ->default('open')
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Instrucciones especiales...')
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('Productos')
                    ->schema([
                        Forms\Components\Repeater::make('orderDetails')
                            ->label('Productos de la Orden')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Producto')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $set('unit_price', $product->sale_price);
                                                        $set('subtotal', $product->sale_price * 1);
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $unitPrice = $get('unit_price') ?? 0;
                                                $set('subtotal', $state * $unitPrice);
                                            }),

                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Precio Unit.')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $quantity = $get('quantity') ?? 1;
                                                $set('subtotal', $state * $quantity);
                                            }),

                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Notas')
                                    ->placeholder('Sin cebolla, extra salsa...')
                                    ->rows(1),
                            ])
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Producto')
                            ->live(),
                    ]),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('discount')
                                    ->label('Descuento')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->default(0),

                                Forms\Components\TextInput::make('tax')
                                    ->label('IGV')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_type')
                    ->label('Servicio')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dine_in' => 'info',
                        'takeout' => 'warning',
                        'delivery' => 'success',
                        'drive_thru' => 'primary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dine_in' => 'Mesa',
                        'takeout' => 'Para Llevar',
                        'delivery' => 'Delivery',
                        'drive_thru' => 'Drive Thru',
                    }),

                Tables\Columns\TextColumn::make('table.number')
                    ->label('Mesa')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Empleado')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'in_preparation' => 'warning',
                        'ready' => 'info',
                        'delivered' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Abierta',
                        'in_preparation' => 'En Preparación',
                        'ready' => 'Lista',
                        'delivered' => 'Entregada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\IconColumn::make('billed')
                    ->label('Facturada')
                    ->boolean(),

                Tables\Columns\TextColumn::make('order_datetime')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'open' => 'Abierta',
                        'in_preparation' => 'En Preparación',
                        'ready' => 'Lista',
                        'delivered' => 'Entregada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ]),

                Tables\Filters\SelectFilter::make('service_type')
                    ->label('Tipo de Servicio')
                    ->options([
                        'dine_in' => 'Mesa',
                        'takeout' => 'Para Llevar',
                        'delivery' => 'Delivery',
                        'drive_thru' => 'Drive Thru',
                    ]),

                Tables\Filters\TernaryFilter::make('billed')
                    ->label('Facturada'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ACCIÓN PARA PROCESAR PAGO - 100% NATIVO
                Tables\Actions\Action::make('process_payment')
                    ->label('Procesar Pago')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn (Order $record) => !$record->isFullyPaid() && $record->status !== 'cancelled')
                    ->form([
                        Forms\Components\Section::make('Información de Pago')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('payment_method')
                                            ->label('Método de Pago')
                                            ->options([
                                                'cash' => 'Efectivo',
                                                'credit_card' => 'Tarjeta de Crédito',
                                                'debit_card' => 'Tarjeta de Débito',
                                                'bank_transfer' => 'Transferencia',
                                                'digital_wallet' => 'Billetera Digital',
                                            ])
                                            ->required(),

                                        Forms\Components\TextInput::make('amount')
                                            ->label('Monto')
                                            ->numeric()
                                            ->prefix('S/')
                                            ->required()
                                            ->maxValue(fn (Order $record) => $record->getRemainingBalance()),
                                    ]),

                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Número de Referencia')
                                    ->placeholder('Opcional para efectivo'),
                            ]),
                    ])
                    ->action(function (Order $record, array $data) {
                        try {
                            $payment = $record->registerPayment(
                                $data['payment_method'],
                                $data['amount'],
                                $data['reference_number'] ?? null
                            );

                            Notification::make()
                                ->title('Pago registrado exitosamente')
                                ->success()
                                ->send();

                            // Si está completamente pagado, actualizar estado
                            if ($record->isFullyPaid()) {
                                $record->update(['status' => 'ready']);

                                Notification::make()
                                    ->title('Orden completamente pagada')
                                    ->body('La orden está lista para preparar/entregar')
                                    ->success()
                                    ->send();
                            }

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al procesar pago')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // ACCIÓN PARA GENERAR FACTURA - 100% NATIVO
                Tables\Actions\Action::make('generate_invoice')
                    ->label('Generar Factura')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->visible(fn (Order $record) => $record->isFullyPaid() && !$record->billed)
                    ->form([
                        Forms\Components\Section::make('Datos de Facturación')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('invoice_type')
                                            ->label('Tipo de Comprobante')
                                            ->options([
                                                'sales_note' => 'Nota de Venta',
                                                'receipt' => 'Boleta',
                                                'invoice' => 'Factura',
                                            ])
                                            ->required()
                                            ->default('sales_note'),

                                        Forms\Components\TextInput::make('series')
                                            ->label('Serie')
                                            ->default('NV001')
                                            ->required(),
                                    ]),

                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre')
                                            ->required(),
                                        Forms\Components\TextInput::make('document_number')
                                            ->label('Documento'),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono'),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email(),
                                    ])
                                    ->required(),
                            ]),
                    ])
                    ->action(function (Order $record, array $data) {
                        try {
                            $invoice = $record->generateInvoice(
                                $data['invoice_type'],
                                $data['series'],
                                $data['customer_id']
                            );

                            if ($invoice) {
                                $record->update(['billed' => true]);
                                $record->completeOrder(); // Libera mesa automáticamente

                                Notification::make()
                                    ->title('Factura generada exitosamente')
                                    ->body("Comprobante N° {$invoice->series}-{$invoice->number}")
                                    ->success()
                                    ->send();
                            }

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al generar factura')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_datetime', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['table', 'customer', 'employee', 'orderDetails.product', 'payments']);
    }
}
