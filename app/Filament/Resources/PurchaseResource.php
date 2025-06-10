<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = '📦 Inventario y Compras';

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
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('business_name')
                                    ->label('Razón Social')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('tax_id')
                                    ->label('RUC')
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('address')
                                    ->label('Dirección')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Teléfono')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('active')
                                    ->label('Activo')
                                    ->default(true),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->label('Nuevo Proveedor')
                                    ->icon('heroicon-m-plus-circle')
                                    ->modalHeading('Crear Nuevo Proveedor')
                                    ->modalWidth('lg');
                            }),

                        Forms\Components\Select::make('warehouse_id')
                            ->label('Almacén')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(function() {
                                return \App\Models\Warehouse::where('is_default', true)->first()?->id;
                            }),

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
                                    })
                                    ->createOptionForm([
                                        Forms\Components\Section::make('Información Básica')
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->label('Código')
                                                    ->required()
                                                    ->unique('products', 'code')
                                                    ->maxLength(20),
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nombre')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Select::make('product_type')
                                                    ->label('Tipo de Producto')
                                                    ->required()
                                                    ->options([
                                                        'ingredient' => 'Ingrediente (insumo de cocina)',
                                                        'both' => 'Producto para venta e ingrediente (ej: gaseosas)'
                                                    ])
                                                    ->default('ingredient')
                                                    ->reactive()
                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                        // Solo mostrar categoría para productos tipo 'both' (gaseosas)
                                                        $set('show_category', $state === 'both');
                                                    }),
                                                Forms\Components\Select::make('category_id')
                                                    ->label('Categoría (solo para gaseosas)')
                                                    ->options(\App\Models\ProductCategory::pluck('name', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->visible(fn (callable $get) => $get('show_category') === true)
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Nombre')
                                                            ->required()
                                                            ->maxLength(50),
                                                        Forms\Components\TextInput::make('description')
                                                            ->label('Descripción')
                                                            ->maxLength(255),
                                                    ])
                                                    ->createOptionUsing(function (array $data) {
                                                        return \App\Models\ProductCategory::create([
                                                            'name' => $data['name'],
                                                            'description' => $data['description'] ?? null,
                                                        ])->id;
                                                    }),
                                                Forms\Components\Hidden::make('show_category')
                                                    ->default(false),
                                            ])
                                            ->columns(2),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Descripción')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Forms\Components\Section::make('Costo y Stock')
                                            ->schema([
                                                Forms\Components\TextInput::make('current_cost')
                                                    ->label('Costo Unitario')
                                                    ->required()
                                                    ->numeric()
                                                    ->default(0),
                                                Forms\Components\TextInput::make('current_stock')
                                                    ->label('Stock Inicial')
                                                    ->helperText('Cantidad inicial del ingrediente que se registrará en el inventario')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->minValue(0.001)
                                                    ->step(0.001),
                                                Forms\Components\DatePicker::make('expiry_date')
                                                    ->label('Fecha de Vencimiento')
                                                    ->helperText('Opcional: Recomendado para ingredientes perecederos')
                                                    ->minDate(now())
                                                    ->displayFormat('d/m/Y'),
                                                Forms\Components\Toggle::make('active')
                                                    ->label('Activo')
                                                    ->default(true),
                                                Forms\Components\Hidden::make('sale_price')
                                                    ->default(0),
                                            ])
                                            ->columns(2),

                                        Forms\Components\Section::make('Información Adicional')
                                            ->schema([
                                                Forms\Components\Placeholder::make('help')
                                                    ->label('Ayuda')
                                                    ->content('- Seleccione "Ingrediente" para insumos de cocina que solo se usan en recetas.\n- Seleccione "Producto para venta e ingrediente" solo para productos como gaseosas que se venden directamente.\n- La categoría solo es necesaria para productos tipo gaseosas.\n- El precio de venta se calculará automáticamente en el módulo de recetas para los productos finales.\n- La fecha de vencimiento es importante para el sistema FIFO (primero en entrar, primero en salir) y para ingredientes perecederos.\n- El stock inicial es obligatorio y se registrará automáticamente en el inventario del almacén predeterminado.')
                                            ])
                                            ->collapsed(),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        // Asegurarse de que solo se creen ingredientes o productos de tipo "both"
                                        if (!in_array($data['product_type'], ['ingredient', 'both'])) {
                                            $data['product_type'] = 'ingredient';
                                        }

                                        // Preparar los datos para crear el producto
                                        $productData = [
                                            'code' => $data['code'],
                                            'name' => $data['name'],
                                            'description' => $data['description'] ?? null,
                                            'product_type' => $data['product_type'],
                                            'current_cost' => $data['current_cost'],
                                            'sale_price' => $data['sale_price'] ?? 0, // Valor por defecto
                                            'current_stock' => $data['current_stock'] ?? 0,
                                            'active' => $data['active'] ?? true,
                                            'available' => true,
                                        ];

                                        // Solo agregar category_id si es un producto tipo 'both' (gaseosas)
                                        if ($data['product_type'] === 'both' && isset($data['category_id'])) {
                                            $productData['category_id'] = $data['category_id'];
                                        } else {
                                            // Para ingredientes, usar una categoría por defecto o null
                                            // Buscar o crear una categoría "Ingredientes" para agrupar todos los ingredientes
                                            $ingredientCategory = \App\Models\ProductCategory::firstOrCreate(
                                                ['name' => 'Ingredientes'],
                                                ['description' => 'Categoría para ingredientes de cocina']
                                            );
                                            $productData['category_id'] = $ingredientCategory->id;
                                        }

                                        // Crear el producto
                                        $product = \App\Models\Product::create($productData);

                                        // Cuando se crea un ingrediente desde compras, siempre se debe crear con stock inicial
                                        // El stock inicial se establece en el campo current_stock del formulario
                                        // y se registra en la tabla ingredient_stock
                                        if ($product->isIngredient()) {
                                            // Obtener el almacén predeterminado
                                            $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();

                                            if ($defaultWarehouse) {
                                                // Crear un registro de stock para el ingrediente
                                                \App\Models\IngredientStock::create([
                                                    'ingredient_id' => $product->id,
                                                    'warehouse_id' => $defaultWarehouse->id,
                                                    'quantity' => $data['current_stock'] ?? 0,
                                                    'unit_cost' => $data['current_cost'],
                                                    'expiry_date' => $data['expiry_date'] ?? null,
                                                    'status' => 'available'
                                                ]);
                                            }
                                        }

                                        return $product->id;
                                    })
                                    ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                        return $action
                                            ->label('Nuevo Ingrediente/Insumo')
                                            ->icon('heroicon-m-plus-circle')
                                            ->modalHeading('Crear Nuevo Ingrediente o Insumo')
                                            ->modalDescription('Registre un nuevo ingrediente para cocina o un producto que se compra (como gaseosas, agua, etc.)')
                                            ->modalWidth('lg');
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

                                Forms\Components\DatePicker::make('expiry_date')
                                    ->label('Fecha de Vencimiento')
                                    ->helperText('Opcional: Recomendado para ingredientes perecederos')
                                    ->minDate(now())
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                $state['product_id']
                                    ? \App\Models\Product::find($state['product_id'])?->name . ' - ' . ($state['quantity'] ?? '?') . ' x S/' . ($state['unit_cost'] ?? '0')
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

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Almacén')
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

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

                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Almacén')
                    ->relationship('warehouse', 'name'),

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
