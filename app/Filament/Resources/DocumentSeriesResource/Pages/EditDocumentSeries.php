<?php

namespace App\Filament\Resources\DocumentSeriesResource\Pages;

use App\Filament\Resources\DocumentSeriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentSeries extends EditRecord
{
    protected static string $resource = DocumentSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
