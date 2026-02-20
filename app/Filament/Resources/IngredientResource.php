<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Filament\Resources\IngredientResource\RelationManagers;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventario y Compras';

    protected static ?string $navigationLabel = 'Ingredientes';

    protected static ?string $modelLabel = 'Ingrediente';

    protected static ?string $pluralModelLabel = 'Ingredientes';

    protected static ?string $slug = 'inventario/ingredientes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('unit_of_measure')
                            ->label('Unidad de Medida')
                            ->options([
                                'g' => 'Gramos (g)',
                                'kg' => 'Kilogramos (kg)',
                                'ml' => 'Mililitros (ml)',
                                'l' => 'Litros (l)',
                                'unidad' => 'Unidades',
                                'pieza' => 'Piezas',
                                'porción' => 'Porciones',
                            ])
                            ->required(),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Proveedor')
                            ->relationship('supplier', 'business_name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stock y Costos')
                    ->schema([
                        Forms\Components\TextInput::make('current_stock')
                            ->label('Stock Actual')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.001),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Stock Mínimo')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.001),

                        Forms\Components\TextInput::make('current_cost')
                            ->label('Costo Actual')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('S/')
                            ->default(0),

                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stock Actual')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->color(fn (Ingredient $ingredient) =>
                        $ingredient->current_stock <= $ingredient->min_stock
                            ? Color::Red
                            : ($ingredient->current_stock <= $ingredient->min_stock * 1.2
                                ? Color::Orange
                                : Color::Green)
                    ),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stock Mínimo')
                    ->numeric(decimalPlaces: 3)
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_cost')
                    ->label('Costo Actual')
                    ->money('PEN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.business_name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
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
                Tables\Filters\SelectFilter::make('active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'business_name'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereColumn('current_stock', '<=', 'min_stock')
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('generate_purchase')
                    ->label('Generar Compra')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Ingredient $ingredient) {
                        // Crear una orden de compra para este ingrediente
                        $quantity = max($ingredient->min_stock * 2 - $ingredient->current_stock, 0);

                        if ($quantity <= 0) {
                            Notification::make()
                                ->warning()
                                ->title('No se requiere reordenar')
                                ->body('El stock actual es suficiente.')
                                ->send();
                            return;
                        }

                        if (!$ingredient->supplier_id) {
                            Notification::make()
                                ->warning()
                                ->title('No hay proveedor asignado')
                                ->body('Asigne un proveedor al ingrediente primero.')
                                ->send();
                            return;
                        }

                        // Aquí podríamos crear la orden de compra
                        // Por ahora, solo mostramos una notificación
                        Notification::make()
                            ->success()
                            ->title('Orden de Compra Generada')
                            ->body("Se ha creado una orden de compra para {$quantity} {$ingredient->unit_of_measure} de {$ingredient->name}.")
                            ->send();
                    })
                    ->visible(fn (Ingredient $ingredient) => $ingredient->isLowStock()),
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

    public static function getRelations(): array
    {
        return [
            // Relaciones como recetas que usan este ingrediente, historial de movimientos, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Mostrar cantidad de ingredientes con stock bajo
        return self::getModel()::query()
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
