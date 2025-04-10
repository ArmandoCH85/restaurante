<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('admin.cash-register.print', ['cashRegister' => $this->record]))
                ->openUrlInNewTab(),

            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'open'),
        ];
    }
}
