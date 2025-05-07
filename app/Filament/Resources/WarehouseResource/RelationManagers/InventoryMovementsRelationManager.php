<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use App\Models\InventoryMovement;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Movimientos de Inventario';

    protected static ?string $modelLabel = 'Movimiento';

    protected static ?string $pluralModelLabel = 'Movimientos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('movement_type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        InventoryMovement::TYPE_PURCHASE => 'Compra',
                        InventoryMovement::TYPE_SALE => 'Venta',
                        InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste',
                        InventoryMovement::TYPE_WASTE => 'Merma',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('unit_cost')
                    ->label('Costo Unitario')
                    ->numeric()
                    ->prefix('S/')
                    ->required(),
                Forms\Components\TextInput::make('reference_document')
                    ->label('Documento de Referencia')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('movement_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        InventoryMovement::TYPE_PURCHASE => 'success',
                        InventoryMovement::TYPE_SALE => 'danger',
                        InventoryMovement::TYPE_ADJUSTMENT => 'warning',
                        InventoryMovement::TYPE_WASTE => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        InventoryMovement::TYPE_PURCHASE => 'Compra',
                        InventoryMovement::TYPE_SALE => 'Venta',
                        InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste',
                        InventoryMovement::TYPE_WASTE => 'Merma',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Costo Unitario')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_document')
                    ->label('Documento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creado por')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        InventoryMovement::TYPE_PURCHASE => 'Compra',
                        InventoryMovement::TYPE_SALE => 'Venta',
                        InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste',
                        InventoryMovement::TYPE_WASTE => 'Merma',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label('Fecha de Creación'),
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
