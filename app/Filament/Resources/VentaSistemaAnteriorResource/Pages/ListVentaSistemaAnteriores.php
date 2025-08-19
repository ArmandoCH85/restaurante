<?php

namespace App\Filament\Resources\VentaSistemaAnteriorResource\Pages;

use App\Filament\Resources\VentaSistemaAnteriorResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListVentaSistemaAnteriores extends ListRecords
{
    protected static string $resource = VentaSistemaAnteriorResource::class;
    
    protected ?string $maxContentWidth = 'full';

    protected function getHeaderActions(): array
    {
        $total = \App\Models\VentaSistemaAnterior::sum('total');
        
        return [
            \Filament\Actions\Action::make('total_general')
                ->label('Total General: S/ ' . number_format($total, 2))
                ->color('success')
                ->disabled()
                ->icon('heroicon-o-currency-dollar'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}