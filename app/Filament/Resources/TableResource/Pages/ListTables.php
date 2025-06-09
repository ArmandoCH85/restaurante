<?php

namespace App\Filament\Resources\TableResource\Pages;

use App\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Route;

class ListTables extends ListRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('viewTableMap')
                ->label('Ver Mapa de Mesas')
                ->icon('heroicon-o-map')
                ->url(fn(): string => '/admin/mapa-mesas'),
        ];
    }
}
