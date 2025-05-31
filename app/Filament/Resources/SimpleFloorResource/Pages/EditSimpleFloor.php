<?php

namespace App\Filament\Resources\SimpleFloorResource\Pages;

use App\Filament\Resources\SimpleFloorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimpleFloor extends EditRecord
{
    protected static string $resource = SimpleFloorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
