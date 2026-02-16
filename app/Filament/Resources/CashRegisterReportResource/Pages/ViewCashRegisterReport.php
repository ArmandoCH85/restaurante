<?php

namespace App\Filament\Resources\CashRegisterReportResource\Pages;

use App\Filament\Resources\CashRegisterReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegisterReport extends ViewRecord
{
    protected static string $resource = CashRegisterReportResource::class;
    protected static string $view = 'filament.resources.cash-register-report-resource.pages.view';

    protected function afterMount(): void
    {
        $this->record->load([
            'openedBy',
            'closedBy',
            'cashMovements.approvedByUser',
            'orders.user',
            'orders.payments'
        ]);
    }
}