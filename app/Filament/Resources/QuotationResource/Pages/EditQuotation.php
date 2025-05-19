<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Quotation;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (Quotation $record) => !$record->isConverted()),

            Actions\Action::make('print')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.quotations.print', ['quotation' => $this->record]))
                ->openUrlInNewTab(),

            Actions\Action::make('download')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.quotations.download', ['quotation' => $this->record]))
                ->openUrlInNewTab(),
        ];
    }

    protected function afterSave(): void
    {
        // Recalcular totales después de guardar la cotización
        $this->record->refresh(); // Asegurarse de tener los datos más recientes
        $this->record->recalculateTotals();

        // Registrar para depuración
        \Illuminate\Support\Facades\Log::info('Cotización actualizada y totales recalculados', [
            'id' => $this->record->id,
            'subtotal' => $this->record->subtotal,
            'tax' => $this->record->tax,
            'discount' => $this->record->discount,
            'total' => $this->record->total
        ]);
    }
}
