<?php

namespace App\Filament\Resources\CashRegisterReportResource\Pages;

use App\Filament\Resources\CashRegisterReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms;
use Illuminate\Support\Collection;

class ListCashRegisterReports extends ListRecords
{
    protected static string $resource = CashRegisterReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Exportar')
                ->action(function (array $data) {
                    // Lógica de exportación
                })
                ->form([
                    Forms\Components\Select::make('format')
                        ->label('Formato')
                        ->options([
                            'pdf' => 'PDF',
                            'excel' => 'Excel',
                        ])
                        ->required(),
                ]),
        ];
    }
}
