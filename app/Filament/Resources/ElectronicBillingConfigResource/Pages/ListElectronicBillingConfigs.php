<?php

namespace App\Filament\Resources\ElectronicBillingConfigResource\Pages;

use App\Filament\Resources\ElectronicBillingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElectronicBillingConfigs extends ListRecords
{
    protected static string $resource = ElectronicBillingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
