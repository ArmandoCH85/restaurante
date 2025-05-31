<?php

namespace App\Filament\Resources\SimpleFloorResource\Pages;

use App\Filament\Resources\SimpleFloorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimpleFloors extends ListRecords
{
    protected static string $resource = SimpleFloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
