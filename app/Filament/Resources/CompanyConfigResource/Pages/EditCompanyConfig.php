<?php

namespace App\Filament\Resources\CompanyConfigResource\Pages;

use App\Filament\Resources\CompanyConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyConfig extends EditRecord
{
    protected static string $resource = CompanyConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
