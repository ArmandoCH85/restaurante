<?php

namespace App\Filament\Resources\CashRegisterReportResource\Pages;

use App\Filament\Resources\CashRegisterReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegisterReport extends ViewRecord
{
    protected static string $resource = CashRegisterReportResource::class;
    protected static string $view = 'filament.resources.cash-register-report-resource.pages.view';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Eager load relationships needed for the view
        $this->record->load([
            'openedBy',
            'closedBy',
            'cashMovements.approvedByUser',
            'cashRegisterExpenses',
            'payments',
            'invoices.customer',
            'orders.user',
            'orders.payments'
        ]);
    }
}
