<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Product;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class WarehouseResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $modelLabel = 'Almacén';

    protected static ?string $pluralModelLabel = 'Almacén';

    protected static ?string $navigationLabel = 'Almacén';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('products.*')
            ->addSelect([
                'current_stock' => InventoryMovement::query()
                    ->selectRaw('SUM(quantity)')
                    ->whereColumn('product_id', 'products.id')
                    ->groupBy('product_id')
                    ->limit(1),
                'monthly_usage' => DB::raw('(
                    SELECT COALESCE(SUM(od.quantity), 0)
                    FROM order_details od
                    JOIN orders o ON od.order_id = o.id
                    WHERE od.product_id = products.id
                    AND o.order_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND o.status = "completed"
                )')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ingredient' => 'warning',
                        'sale_item' => 'success',
                        'both' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ingredient' => 'Insumo',
                        'sale_item' => 'Producto',
                        'both' => 'Ambos',
                    }),

                TextColumn::make('current_stock')
                    ->label('Stock Actual')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->current_stock === null) {
                            return 'gray';
                        }
                        
                        // Calcular el umbral de alerta basado en el uso mensual
                        $minStockThreshold = max(5, $record->monthly_usage * 0.2); // 20% del uso mensual o mínimo 5
                        
                        if ($record->current_stock <= 0) {
                            return 'danger';
                        } elseif ($record->current_stock < $minStockThreshold) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    })
                    ->description(function ($record) {
                        if ($record->current_stock === null) {
                            return 'Sin movimientos';
                        }
                        
                        $minStockThreshold = max(5, $record->monthly_usage * 0.2);
                        if ($record->current_stock < $minStockThreshold && $record->current_stock > 0) {
                            return 'Stock bajo - Reordenar';
                        }
                        return null;
                    }),
                    
                TextColumn::make('monthly_usage')
                    ->label('Uso Mensual')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('current_cost')
                    ->label('Costo Actual')
                    ->money('PEN')
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->label('Precio Venta')
                    ->money('PEN')
                    ->sortable(),

                IconColumn::make('has_recipe')
                    ->label('Receta')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('product_type')
                    ->label('Tipo de Producto')
                    ->options([
                        'ingredient' => 'Insumo',
                        'sale_item' => 'Producto',
                        'both' => 'Ambos',
                    ]),
                
                SelectFilter::make('stock_status')
                    ->label('Estado de Stock')
                    ->options([
                        'low' => 'Stock Bajo',
                        'out' => 'Sin Stock',
                        'available' => 'Disponible',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'low' => $query->whereRaw('(
                                SELECT SUM(im.quantity) 
                                FROM inventory_movements im 
                                WHERE im.product_id = products.id
                            ) > 0 AND (
                                SELECT SUM(im.quantity) 
                                FROM inventory_movements im 
                                WHERE im.product_id = products.id
                            ) < GREATEST(5, (
                                SELECT COALESCE(SUM(od.quantity), 0) * 0.2
                                FROM order_details od
                                JOIN orders o ON od.order_id = o.id
                                WHERE od.product_id = products.id
                                AND o.order_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                AND o.status = "completed"
                            ))'),
                            'out' => $query->whereRaw('(
                                SELECT COALESCE(SUM(quantity), 0) 
                                FROM inventory_movements 
                                WHERE product_id = products.id
                            ) <= 0'),
                            'available' => $query->whereRaw('(
                                SELECT COALESCE(SUM(quantity), 0) 
                                FROM inventory_movements 
                                WHERE product_id = products.id
                            ) >= GREATEST(5, (
                                SELECT COALESCE(SUM(od.quantity), 0) * 0.2
                                FROM order_details od
                                JOIN orders o ON od.order_id = o.id
                                WHERE od.product_id = products.id
                                AND o.order_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                AND o.status = "completed"
                            ))'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListWarehouse::route('/'),
            'view' => Pages\ViewWarehouse::route('/{record}'),
        ];
    }
}