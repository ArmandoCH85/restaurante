<?php

namespace App\Filament\Resources\ElectronicBillingConfigResource\Pages;

use App\Filament\Resources\ElectronicBillingConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElectronicBillingConfig extends EditRecord
{
    protected static string $resource = ElectronicBillingConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
