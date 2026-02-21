<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Model;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    // Personalizar el título de la página
    public function getTitle(): string
    {
        return 'Crear Nueva Proforma';
    }

    // Personalizar el subtítulo de la página
    public function getSubheading(): string
    {
        return 'Complete el formulario para crear una nueva proforma';
    }

    // Personalizar la descripción de la página
    public function getDescription(): string
    {
        return 'Las proformas permiten ofrecer precios a los clientes antes de generar un pedido.';
    }

    // Personalizar los botones de acción
    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('volver')
                ->label('Volver al listado')
                ->url(QuotationResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurarse de que el número de cotización sea único
        $data['quotation_number'] = Quotation::generateQuotationNumber();

        // Establecer valores predeterminados para los campos numéricos
        $data['subtotal'] = $data['subtotal'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;
        $data['total'] = $data['total'] ?? 0;

        // Establecer el usuario actual como creador
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalcular totales después de crear la cotización
        $this->record->refresh(); // Asegurarse de tener los datos más recientes
        $this->record->recalculateTotals();

        // Registrar para depuración
        \Illuminate\Support\Facades\Log::info('Cotización creada y totales recalculados', [
            'id' => $this->record->id,
            'subtotal' => $this->record->subtotal,
            'tax' => $this->record->tax,
            'discount' => $this->record->discount,
            'total' => $this->record->total
        ]);
    }

    // Personalizar el mensaje de éxito
    public function getCreatedNotificationTitle(): string
    {
        return 'Proforma creada correctamente';
    }

    // Personalizar el botón de guardar
    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Crear Proforma')
                ->icon('heroicon-o-document-plus'),

            $this->getCancelFormAction()
                ->label('Cancelar')
                ->color('gray'),
        ];
    }
}
