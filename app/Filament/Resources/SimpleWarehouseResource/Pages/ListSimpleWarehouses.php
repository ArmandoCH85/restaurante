<?php

namespace App\Filament\Resources\SimpleWarehouseResource\Pages;

use App\Filament\Resources\SimpleWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimpleWarehouses extends ListRecords
{
    protected static string $resource = SimpleWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
