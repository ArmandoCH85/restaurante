<?php

namespace App\Filament\Resources\SimpleWarehouseResource\Pages;

use App\Filament\Resources\SimpleWarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimpleWarehouse extends EditRecord
{
    protected static string $resource = SimpleWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
