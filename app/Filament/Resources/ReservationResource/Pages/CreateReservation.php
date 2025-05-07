<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Table as TableModel;
use App\Services\ReservationService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Request;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    public ?int $tableId = null;

    public function mount(): void
    {
        // Obtener el ID de la mesa desde la URL si existe
        $this->tableId = Request::query('table_id');

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validar disponibilidad de la mesa
        if (isset($data['table_id']) && $data['status'] === 'confirmed') {
            $reservationService = new ReservationService();
            $isAvailable = $reservationService->isTableAvailable(
                $data['table_id'],
                $data['reservation_date'],
                $data['reservation_time']
            );

            if (!$isAvailable) {
                Notification::make()
                    ->title('Mesa no disponible')
                    ->body('La mesa seleccionada no está disponible para la fecha y hora indicadas.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Usar el servicio para actualizar el estado de la mesa si es necesario
        $reservation = $this->record;

        if ($reservation->isConfirmed() && $reservation->table_id) {
            $reservationService = new ReservationService();

            // Actualizar explícitamente el estado de la mesa a reservada
            $table = TableModel::find($reservation->table_id);
            if ($table) {
                $table->status = TableModel::STATUS_RESERVED;
                $table->save();

                Notification::make()
                    ->title('Mesa reservada')
                    ->body('La mesa ha sido marcada como reservada.')
                    ->success()
                    ->send();
            }
        }
    }
}
