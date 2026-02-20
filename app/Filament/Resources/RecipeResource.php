<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers;
use App\Models\Recipe;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Menu y Carta';

    protected static ?string $navigationLabel = 'Recetas';

    protected static ?string $modelLabel = 'Receta';

    protected static ?string $pluralModelLabel = 'Recetas';

    protected static ?string $slug = 'resources/recipes';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship(
                                'product',
                                'name',
                                fn (Builder $query) => $query
                                    ->where('product_type', 'sale_item')
                                    ->orWhere('product_type', 'both')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('sale_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('S/'),
                                Forms\Components\Select::make('product_type')
                                    ->options([
                                        'sale_item' => 'Artículo de Venta',
                                        'both' => 'Ambos (Venta e Ingrediente)',
                                    ])
                                    ->required()
                                    ->default('sale_item'),
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),
                            ]),

                        Forms\Components\TextInput::make('preparation_time')
                            ->label('Tiempo de Preparación (min)')
                            ->required()
                            ->numeric()
                            ->default(10)
                            ->minValue(0),

                        Forms\Components\Textarea::make('preparation_instructions')
                            ->label('Instrucciones de Preparación')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ingredientes')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('ingredient_id')
                                    ->label('Ingrediente')
                                    ->relationship(
                                        'ingredient',
                                        'name',
                                        fn (Builder $query) => $query
                                            ->where('active', true)
                                            ->where(function ($query) {
                                                $query->where('product_type', 'ingredient')
                                                      ->orWhere('product_type', 'both');
                                            })
                                    )
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->searchDebounce(500)
                                    ->searchingMessage('Buscando ingredientes...')
                                    ->noSearchResultsMessage('No se encontraron ingredientes')
                                    ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Product::find($value)?->name ?? null)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            // Default to 'unidad' if no specific unit is available
                                            $set('unit_of_measure', 'unidad');
                                        } else {
                                            $set('unit_of_measure', 'unidad');
                                        }
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->default(1),

                                Forms\Components\TextInput::make('unit_of_measure')
                                    ->label('Unidad')
                                    ->required()
                                    ->readOnly(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['ingredient_id']) && $state['ingredient_id']
                                    ? \App\Models\Product::find($state['ingredient_id'])?->name . ' - ' . ($state['quantity'] ?? '?') . ' ' . ($state['unit_of_measure'] ?? '')
                                    : 'Seleccione un ingrediente'
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('Ingredientes')
                    ->counts('details')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expected_cost')
                    ->label('Costo Esperado')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('preparation_time')
                    ->label('Tiempo (min)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('recalcular_costo')
                    ->label('Recalcular Costo')
                    ->icon('heroicon-o-calculator')
                    ->action(function (Recipe $recipe) {
                        $recipe->updateExpectedCost();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Producto'),

                        Infolists\Components\TextEntry::make('product.code')
                            ->label('Código'),

                        Infolists\Components\TextEntry::make('expected_cost')
                            ->label('Costo Esperado')
                            ->money('PEN'),

                        Infolists\Components\TextEntry::make('preparation_time')
                            ->label('Tiempo de Preparación')
                            ->suffix(' minutos'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Instrucciones de Preparación')
                    ->schema([
                        Infolists\Components\TextEntry::make('preparation_instructions')
                            ->label('')
                            ->markdown(),
                    ]),

                Infolists\Components\Section::make('Ingredientes')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('details')
                            ->schema([
                                Infolists\Components\TextEntry::make('ingredient.name')
                                    ->label('Ingrediente')
                                    ->getStateUsing(fn ($record) => $record->ingredient?->name ?? 'Ingrediente no encontrado'),

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric(decimalPlaces: 3)
                                    ->suffix(fn ($record) => ' ' . $record->unit_of_measure),

                                Infolists\Components\TextEntry::make('getIngredientCost')
                                    ->label('Costo')
                                    ->state(fn ($record) => $record->getIngredientCost())
                                    ->money('PEN'),
                            ])
                            ->columns(3),
                ]),
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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
