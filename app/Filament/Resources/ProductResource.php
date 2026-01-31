<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->description('Ingrese la información principal del producto')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Ingrese el código del producto'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ingrese el nombre del producto'),

                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('description')
                                    ->label('Descripción')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Select::make('product_type')
                            ->label('Tipo de Producto')
                            ->required()
                            ->options([
                                'ingredient' => 'Ingrediente',
                                'sale_item' => 'Artículo de Venta',
                                'both' => 'Ambos'
                            ])
                            ->default('sale_item')
                            ->native(false),

                        Forms\Components\Select::make('ingredient_id')
                            ->label('Ingrediente')
                            ->options(function () {
                                return \App\Models\Ingredient::query()
                                    ->where('active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->searchDebounce(500)
                            ->searchingMessage('Buscando ingredientes...')
                            ->noSearchResultsMessage('No se encontraron ingredientes')
                            ->visible(fn (callable $get) => in_array($get('product_type'), ['ingredient', 'both'])),
                    ])->columns(2),

                Forms\Components\Section::make('Precios y Costos')
                    ->description('Configure los precios y costos del producto')
                    ->schema([
                        Forms\Components\TextInput::make('sale_price')
                            ->label('Precio de Venta')
                            ->required()
                            ->numeric()
                            ->prefix('S/')
                            ->maxValue(99999999.99)
                            ->step(0.01),

                        Forms\Components\TextInput::make('current_cost')
                            ->label('Costo Actual')
                            ->required()
                            ->numeric()
                            ->prefix('S/')
                            ->default(0.00)
                            ->maxValue(99999999.99)
                            ->step(0.01),

                        Forms\Components\TextInput::make('current_stock')
                            ->label('Stock Inicial')
                            ->numeric()
                            ->default(0.00)
                            ->step(0.001)
                            ->disabled()
                            ->helperText('El stock inicial solo puede ser establecido al crear un ingrediente desde el módulo de compras.'),
                    ])->columns(2),

                Forms\Components\Section::make('Detalles Adicionales')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->placeholder('Ingrese una descripción detallada del producto')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Imagen')
                            ->image()
                            ->imageEditor()
                            ->helperText('Sin límite de tamaño')
                            ->directory('productos')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Estado y Configuración')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->required()
                            ->default(true)
                            ->helperText('Determina si el producto está activo en el sistema'),

                        Forms\Components\Toggle::make('available')
                            ->label('Disponible')
                            ->required()
                            ->default(true)
                            ->helperText('Determina si el producto está disponible para la venta'),

                        Forms\Components\Toggle::make('has_recipe')
                            ->label('Tiene Receta')
                            ->required()
                            ->default(false)
                            ->helperText('Indica si el producto tiene una receta asociada'),
                    ])->columns(3),
            ]);
    }

    /**
     * OPTIMIZACIÓN: Agregar eager loading para evitar N+1 queries
     * y filtrar solo productos de venta, excluyendo ingredientes puros
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'recipe'])
            ->where(function(\Illuminate\Database\Eloquent\Builder $query) {
                $query->where('product_type', Product::TYPE_SALE_ITEM)
                      ->orWhere('product_type', Product::TYPE_BOTH);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-product.png')),

                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingredient' => 'warning',
                        'sale_item' => 'success',
                        'both' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ingredient' => 'Ingrediente',
                        'sale_item' => 'Artículo',
                        'both' => 'Ambos',
                    }),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_cost')
                    ->label('Costo')
                    ->money('PEN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('available')
                    ->label('Disponible')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_recipe')
                    ->label('Receta')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Tipo de Producto')
                    ->options([
                        'ingredient' => 'Ingrediente',
                        'sale_item' => 'Artículo de Venta',
                        'both' => 'Ambos'
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Filters\TernaryFilter::make('available')
                    ->label('Disponible')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar Seleccionados'),
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
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return '🍽️ Menú y Carta';
    }
}
