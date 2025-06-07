<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use App\Models\Warehouse;
use Filament\Tables\Filters\Layout;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\IngredientStock;

class ListWarehouseInventories extends ListRecords
{
    protected static string $resource = WarehouseInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No se requieren acciones adicionales en el encabezado
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getTableDescription(): ?string
    {
        $activeWarehouseCount = Warehouse::where('active', true)->count();
        $ingredientStocksCount = IngredientStock::where('status', '!=', 'expired')->count();
        
        return "Gestión de inventario por almacén. Actualmente hay {$activeWarehouseCount} almacenes activos con {$ingredientStocksCount} registros de stock en total.";
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'warehouse.name';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'asc';
    }

    protected function getTableFiltersFormColumns(): int
    {
        return 3;
    }

    protected function getTableFiltersLayout(): ?string
    {
        return Layout::AboveContent;
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No hay ingredientes en inventario';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Los ingredientes aparecerán aquí cuando se registren compras y se asignen a almacenes.';
    }
}
