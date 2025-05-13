<?php

namespace App\Filament\Resources\CompanyConfigResource\Pages;

use App\Filament\Resources\CompanyConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyConfigs extends ListRecords
{
    protected static string $resource = CompanyConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
