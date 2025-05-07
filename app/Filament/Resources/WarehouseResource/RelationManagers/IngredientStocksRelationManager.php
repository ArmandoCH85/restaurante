<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Ingredient;
use App\Models\IngredientStock;

class IngredientStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredientStocks';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Stock de Ingredientes';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Stocks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('ingredient_id')
                    ->label('Ingrediente')
                    ->options(Ingredient::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('unit_cost')
                    ->label('Costo Unitario')
                    ->numeric()
                    ->prefix('S/')
                    ->required(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Fecha de Vencimiento'),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        IngredientStock::STATUS_AVAILABLE => 'Disponible',
                        IngredientStock::STATUS_RESERVED => 'Reservado',
                        IngredientStock::STATUS_EXPIRED => 'Vencido',
                    ])
                    ->default(IngredientStock::STATUS_AVAILABLE)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('ingredient.name')
                    ->label('Ingrediente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Costo Unitario')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        IngredientStock::STATUS_AVAILABLE => 'success',
                        IngredientStock::STATUS_RESERVED => 'warning',
                        IngredientStock::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        IngredientStock::STATUS_AVAILABLE => 'Disponible',
                        IngredientStock::STATUS_RESERVED => 'Reservado',
                        IngredientStock::STATUS_EXPIRED => 'Vencido',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Ingreso')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        IngredientStock::STATUS_AVAILABLE => 'Disponible',
                        IngredientStock::STATUS_RESERVED => 'Reservado',
                        IngredientStock::STATUS_EXPIRED => 'Vencido',
                    ]),
                Tables\Filters\Filter::make('expiry_date')
                    ->form([
                        Forms\Components\DatePicker::make('expiry_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('expiry_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expiry_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expiry_date', '>=', $date),
                            )
                            ->when(
                                $data['expiry_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expiry_date', '<=', $date),
                            );
                    })
                    ->label('Fecha de Vencimiento'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
