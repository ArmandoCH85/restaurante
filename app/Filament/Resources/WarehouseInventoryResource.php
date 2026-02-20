<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseInventoryResource\Pages;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Model;

class WarehouseInventoryResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Inventario y Compras';

    protected static ?string $navigationLabel = 'Inventario por Almacén';

    protected static ?string $modelLabel = 'Stock de Ingrediente';

    protected static ?string $pluralModelLabel = 'Inventario por Almacén';

    protected static ?string $slug = 'inventario/por-almacen';

    protected static ?int $navigationSort = 4;

    public static function getGloballySearchableAttributes(): array
    {
        return ['ingredient.name', 'ingredient.code', 'warehouse.name'];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('active', true)
            ->withCount(['ingredientStocks as stock_count']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información del Almacén')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled(),

                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->required()
                                    ->maxLength(20)
                                    ->disabled(),

                                Forms\Components\TextInput::make('location')
                                    ->label('Ubicación')
                                    ->maxLength(255)
                                    ->disabled(),

                                Forms\Components\Toggle::make('is_default')
                                    ->label('Almacén Principal')
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicación')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock_count')
                    ->label('Ingredientes')
                    ->counts('ingredientStocks')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Principal')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
                Tables\Filters\Filter::make('with_ingredients')
                    ->label('Con Ingredientes')
                    ->query(fn (Builder $query): Builder => $query->has('ingredientStocks')),

                Tables\Filters\Filter::make('without_ingredients')
                    ->label('Sin Ingredientes')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('ingredientStocks')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Inventario')
                    ->visible(fn (Warehouse $record): bool => $record->ingredientStocks()->count() > 0),
            ])
            ->bulkActions([
                // No bulk actions needed for this view
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // No relations needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouseInventories::route('/'),
            'view' => Pages\ViewWarehouseInventory::route('/{record}'),
        ];
    }
}
