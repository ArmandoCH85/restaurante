<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Services\ReservationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('confirm')
                ->label('Confirmar')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->action(function () {
                    $reservationService = new ReservationService();
                    $reservationService->updateReservation($this->record, ['status' => 'confirmed']);
                    
                    Notification::make()
                        ->title('Reserva confirmada')
                        ->success()
                        ->send();
                        
                    $this->redirect(ReservationResource::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['pending', 'confirmed']))
                ->requiresConfirmation()
                ->action(function () {
                    $reservationService = new ReservationService();
                    $reservationService->cancelReservation($this->record);
                    
                    Notification::make()
                        ->title('Reserva cancelada')
                        ->success()
                        ->send();
                        
                    $this->redirect(ReservationResource::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\Action::make('complete')
                ->label('Completar')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn () => $this->record->status === 'confirmed')
                ->requiresConfirmation()
                ->action(function () {
                    $reservationService = new ReservationService();
                    $reservationService->completeReservation($this->record);
                    
                    Notification::make()
                        ->title('Reserva completada')
                        ->success()
                        ->send();
                        
                    $this->redirect(ReservationResource::getUrl('edit', ['record' => $this->record]));
                }),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validar disponibilidad de la mesa
        if (isset($data['table_id']) && $data['status'] === 'confirmed' && 
            ($data['table_id'] != $this->record->table_id || 
             $data['reservation_date'] != $this->record->reservation_date->format('Y-m-d') || 
             $data['reservation_time'] != $this->record->reservation_time)) {
            
            $reservationService = new ReservationService();
            $isAvailable = $reservationService->isTableAvailable(
                $data['table_id'],
                $data['reservation_date'],
                $data['reservation_time'],
                $this->record->id
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
    
    protected function afterSave(): void
    {
        // Usar el servicio para actualizar el estado de la mesa si es necesario
        $reservationService = new ReservationService();
        $reservationService->updateReservation($this->record, [
            'status' => $this->record->status
        ]);
    }
}
