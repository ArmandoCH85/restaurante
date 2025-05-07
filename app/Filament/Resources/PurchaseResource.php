<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Compras';

    protected static ?string $modelLabel = 'Compra';

    protected static ?string $pluralModelLabel = 'Compras';

    protected static ?string $slug = 'inventario/compras';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Compra')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'business_name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Fecha de Compra')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options([
                                'invoice' => 'Factura',
                                'receipt' => 'Boleta',
                                'ticket' => 'Ticket',
                                'dispatch_guide' => 'Guía de Remisión',
                                'other' => 'Otro',
                            ])
                            ->required()
                            ->default('invoice')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                // Si es guía de remisión, sugerir formato
                                if ($state === 'dispatch_guide') {
                                    $set('document_number', 'T001-');
                                }
                            }),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('completed'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Compra')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Producto')
                                    ->options(\App\Models\Product::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state) {
                                            $product = \App\Models\Product::find($state);
                                            if ($product) {
                                                $set('unit_cost', $product->current_cost ?? 0);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $get, callable $set) {
                                        $set('subtotal', $state * $get('unit_cost'));
                                        $set('../../subtotal', collect($get('../../details'))->sum(fn($item) => $item['subtotal'] ?? 0));
                                    }),

                                Forms\Components\TextInput::make('unit_cost')
                                    ->label('Costo Unitario')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $get, callable $set) {
                                        $set('subtotal', $get('quantity') * $state);
                                        $set('../../subtotal', collect($get('../../details'))->sum(fn($item) => $item['subtotal'] ?? 0));
                                    }),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/')
                                    ->disabled()
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                $state['product_id']
                                    ? Ingredient::find($state['product_id'])?->name . ' - ' . ($state['quantity'] ?? '?') . ' x S/' . ($state['unit_cost'] ?? '0')
                                    : null
                            )
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $set('subtotal', collect($get('details'))->sum(fn($item) => $item['subtotal'] ?? 0));
                            }),
                    ]),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('S/')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('tax')
                            ->label('IGV (%)')
                            ->numeric()
                            ->prefix('S/')
                            ->default(18)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('S/')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.business_name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo Doc.')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'invoice' => 'Factura',
                        'receipt' => 'Boleta',
                        'ticket' => 'Ticket',
                        default => 'Otro',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('Núm. Doc.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'business_name'),

                Tables\Filters\Filter::make('purchase_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('register_stock')
                    ->label('Registrar Stock')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Purchase $purchase) => $purchase->status === 'completed')
                    ->action(function (Purchase $purchase) {
                        // Recorrer todos los detalles de la compra
                        foreach ($purchase->details as $detail) {
                            // Buscar el producto (puede ser ingrediente u otro tipo de producto)
                            $product = \App\Models\Product::find($detail->product_id);

                            if ($product) {
                                // Crear movimiento de inventario
                                InventoryMovement::create([
                                    'product_id' => $product->id,
                                    'quantity' => $detail->quantity,
                                    'unit_cost' => $detail->unit_cost,
                                    'movement_type' => 'purchase',
                                    'reference_id' => $purchase->id,
                                    'reference_type' => Purchase::class,
                                    'reference_document' => $purchase->document_number,
                                    'created_by' => $purchase->created_by,
                                    'notes' => "Compra: {$purchase->document_type} {$purchase->document_number}",
                                ]);
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-clipboard-document-check'),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
