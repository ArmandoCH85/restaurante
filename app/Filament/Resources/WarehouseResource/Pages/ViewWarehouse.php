<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        // Obtener datos de uso mensual y stock actual
        $record = $this->getRecord();
        $monthlyUsage = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('order_details.product_id', $record->id)
            ->where('orders.order_datetime', '>=', now()->subDays(30))
            ->where('orders.status', 'completed')
            ->sum('order_details.quantity');
            
        $currentStock = InventoryMovement::where('product_id', $record->id)
            ->sum('quantity');
            
        $minStockThreshold = max(5, $monthlyUsage * 0.2);
        $stockStatus = $currentStock <= 0 ? 'Sin stock' : 
                      ($currentStock < $minStockThreshold ? 'Stock bajo' : 'Stock adecuado');
        $stockColor = $currentStock <= 0 ? 'danger' : 
                     ($currentStock < $minStockThreshold ? 'warning' : 'success');
        
        return $infolist
            ->schema([
                Section::make('Información del Producto')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Código'),
                        
                        TextEntry::make('name')
                            ->label('Nombre'),
                        
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->markdown()
                            ->columnSpanFull(),
                        
                        TextEntry::make('category.name')
                            ->label('Categoría'),
                        
                        TextEntry::make('product_type')
                            ->label('Tipo')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'ingredient' => 'Insumo',
                                'sale_item' => 'Producto',
                                'both' => 'Ambos',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'ingredient' => 'warning',
                                'sale_item' => 'success',
                                'both' => 'info',
                            }),
                        
                        IconEntry::make('has_recipe')
                            ->label('Tiene Receta')
                            ->boolean(),
                        
                        IconEntry::make('active')
                            ->label('Activo')
                            ->boolean(),
                    ])
                    ->columns(2),
                
                Section::make('Información de Stock')
                    ->schema([
                        TextEntry::make('current_stock')
                            ->label('Stock Actual')
                            ->state(function ($record) {
                                return InventoryMovement::where('product_id', $record->id)
                                    ->sum('quantity');
                            })
                            ->numeric()
                            ->color(fn ($state): string => 
                                $state <= 0 ? 'danger' : 
                                ($state < 10 ? 'warning' : 'success')
                            ),
                        
                        TextEntry::make('current_cost')
                            ->label('Costo Actual')
                            ->money('PEN'),
                        
                        TextEntry::make('sale_price')
                            ->label('Precio de Venta')
                            ->money('PEN'),
                        
                        TextEntry::make('profit_margin')
                            ->label('Margen de Ganancia')
                            ->state(function ($record) {
                                if ($record->current_cost > 0) {
                                    $margin = (($record->sale_price - $record->current_cost) / $record->current_cost) * 100;
                                    return number_format($margin, 2) . '%';
                                }
                                return 'N/A';
                            })
                            ->color(fn ($state): string => 
                                $state === 'N/A' ? 'gray' : 
                                (floatval($state) < 20 ? 'danger' : 
                                (floatval($state) < 40 ? 'warning' : 'success'))
                            ),
                    ])
                    ->columns(2),
                
                Section::make('Movimientos de Inventario Recientes')
                    ->schema([
                        TextEntry::make('recent_movements')
                            ->label('Últimos 5 Movimientos')
                            ->state(function ($record) {
                                $movements = InventoryMovement::where('product_id', $record->id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get()
                                    ->map(function ($movement) {
                                        $type = match ($movement->movement_type) {
                                            'purchase' => '🟢 Compra',
                                            'sale' => '🔴 Venta',
                                            'adjustment' => '🟠 Ajuste',
                                            'waste' => '⚫ Merma',
                                            default => $movement->movement_type,
                                        };
                                        
                                        return "{$type}: {$movement->quantity} unidades - {$movement->created_at->format('d/m/Y H:i')}";
                                    })
                                    ->join("\n\n");
                                
                                return $movements ?: 'No hay movimientos recientes';
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Imagen del Producto')
                    ->schema([
                        ImageEntry::make('image_path')
                            ->label('Imagen')
                            ->visibility(fn ($state) => $state !== null)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsible(),
            ]);
    }
}