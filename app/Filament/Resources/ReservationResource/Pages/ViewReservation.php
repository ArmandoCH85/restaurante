<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Services\ReservationService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewReservation extends ViewRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
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
                        
                    $this->redirect(ReservationResource::getUrl('view', ['record' => $this->record]));
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
                        
                    $this->redirect(ReservationResource::getUrl('view', ['record' => $this->record]));
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
                        
                    $this->redirect(ReservationResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
