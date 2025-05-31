<?php

namespace App\Filament\Resources\SimpleTableResource\Pages;

use App\Filament\Resources\SimpleTableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimpleTable extends EditRecord
{
    protected static string $resource = SimpleTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
