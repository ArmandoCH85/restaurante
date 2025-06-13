<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(): void
    {
        parent::mount();

        // Pre-completar formulario si viene table_id
        if (request()->has('table_id')) {
            $this->form->fill([
                'table_id' => request()->get('table_id'),
                'service_type' => 'dine_in',
                'employee_id' => Auth::id(),
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Obtener la caja registradora activa
        $activeCashRegister = \App\Models\CashRegister::getOpenRegister();

        if (!$activeCashRegister) {
            throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
        }

        // Establecer valores por defecto
        $data['employee_id'] = $data['employee_id'] ?? Auth::id();
        $data['order_datetime'] = now();
        $data['status'] = 'open';
        $data['subtotal'] = 0;
        $data['tax'] = 0;
        $data['total'] = 0;
        $data['discount'] = $data['discount'] ?? 0;
        $data['billed'] = false;
        $data['cash_register_id'] = $activeCashRegister->id;

        // Si viene table_id desde el mapa de mesas, pre-asignar
        if (request()->has('table_id') && !isset($data['table_id'])) {
            $data['table_id'] = request()->get('table_id');
            $data['service_type'] = 'dine_in';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalcular totales después de crear
        $this->record->recalculateTotals();

        // Si hay una mesa asignada, marcarla como ocupada
        if ($this->record->table_id && $this->record->service_type === 'dine_in') {
            $table = $this->record->table;
            if ($table && $table->isAvailable()) {
                $table->update(['status' => 'occupied']);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
