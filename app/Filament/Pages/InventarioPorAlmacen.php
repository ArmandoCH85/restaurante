<?php

namespace App\Filament\Pages;

use App\Models\Warehouse;
use App\Models\IngredientStock;
use Filament\Pages\Page;

class InventarioPorAlmacen extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static string $view = 'filament.pages.inventario-por-almacen';
    
    protected static ?string $title = 'Inventario por Almacén';
    
    protected static ?string $navigationLabel = 'Inventario por Almacén';
    
    protected static ?string $slug = 'inventario/resumen-por-almacen';
    
    protected static ?string $navigationGroup = 'Inventario y Compras';
    
    protected static ?int $navigationSort = 1;

    public function getInventoryData()
    {
        // Obtener todos los almacenes activos con ingredientes y productos
        $warehouses = Warehouse::where('active', true)
            ->with([
                'ingredientStocks' => function ($query) {
                    $query->where('status', IngredientStock::STATUS_AVAILABLE)
                          ->where('quantity', '>', 0)
                          ->with('ingredient');
                },
                'productStocks' => function ($query) {
                    $query->where('status', 'available')
                          ->where('quantity', '>', 0)
                          ->with('product');
                }
            ])
            ->get();

        $inventoryData = [];
        $totalGeneral = 0;
        $warehousesWithStock = 0;

        foreach ($warehouses as $warehouse) {
            $warehouseData = [
                'warehouse' => $warehouse,
                'ingredients' => [],
                'products' => [],
                'subtotal' => 0,
                'has_stock' => false
            ];

            // Procesar ingredientes
            foreach ($warehouse->ingredientStocks as $stock) {
                if ($stock->ingredient) {
                    $totalValue = $stock->quantity * $stock->unit_cost;
                    
                    $warehouseData['ingredients'][] = [
                        'ingredient' => $stock->ingredient,
                        'stock' => $stock,
                        'total_value' => $totalValue
                    ];
                    
                    $warehouseData['subtotal'] += $totalValue;
                    $warehouseData['has_stock'] = true;
                }
            }

            // Procesar productos regulares
            foreach ($warehouse->productStocks as $stock) {
                if ($stock->product) {
                    $totalValue = $stock->quantity * $stock->unit_cost;
                    
                    $warehouseData['products'][] = [
                        'product' => $stock->product,
                        'stock' => $stock,
                        'total_value' => $totalValue
                    ];
                    
                    $warehouseData['subtotal'] += $totalValue;
                    $warehouseData['has_stock'] = true;
                }
            }

            // Agregar todos los almacenes (con o sin stock)
            $inventoryData[] = $warehouseData;
            $totalGeneral += $warehouseData['subtotal'];
            
            if ($warehouseData['has_stock']) {
                $warehousesWithStock++;
            }
        }

        return [
            'warehouses' => $inventoryData,
            'total_general' => $totalGeneral,
            'total_warehouses' => count($warehouses),
            'warehouses_with_stock' => $warehousesWithStock
        ];
    }
}
