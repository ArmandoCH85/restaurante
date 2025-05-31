<?php

namespace App\Filament\Resources\SimpleTableResource\Pages;

use App\Filament\Resources\SimpleTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimpleTables extends ListRecords
{
    protected static string $resource = SimpleTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
