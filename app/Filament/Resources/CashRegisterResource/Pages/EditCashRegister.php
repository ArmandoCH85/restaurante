<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditCashRegister extends EditRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calcular el monto total contado a partir de las denominaciones
        $totalCounted = 0;

        // Billetes
        $totalCounted += ($data['bill_10'] ?? 0) * 10;
        $totalCounted += ($data['bill_20'] ?? 0) * 20;
        $totalCounted += ($data['bill_50'] ?? 0) * 50;
        $totalCounted += ($data['bill_100'] ?? 0) * 100;
        $totalCounted += ($data['bill_200'] ?? 0) * 200;

        // Monedas
        $totalCounted += ($data['coin_010'] ?? 0) * 0.1;
        $totalCounted += ($data['coin_020'] ?? 0) * 0.2;
        $totalCounted += ($data['coin_050'] ?? 0) * 0.5;
        $totalCounted += ($data['coin_1'] ?? 0) * 1;
        $totalCounted += ($data['coin_2'] ?? 0) * 2;
        $totalCounted += ($data['coin_5'] ?? 0) * 5;

        // Calcular el monto esperado (monto inicial + ventas en efectivo)
        $expectedAmount = $this->record->calculateExpectedCash();

        // Calcular la diferencia
        $difference = $totalCounted - $expectedAmount;

        // Añadir datos para el cierre de caja
        $data['closed_by'] = Auth::id();
        $data['closing_datetime'] = now();
        $data['is_active'] = false;
        $data['actual_amount'] = $totalCounted;
        $data['expected_amount'] = $expectedAmount;
        $data['difference'] = $difference;

        // Guardar el desglose de denominaciones en las observaciones
        $denominationDetails = "Cierre de caja - Desglose de denominaciones:\n";
        $denominationDetails .= "Billetes: ";
        $denominationDetails .= "S/10: {$data['bill_10']} | ";
        $denominationDetails .= "S/20: {$data['bill_20']} | ";
        $denominationDetails .= "S/50: {$data['bill_50']} | ";
        $denominationDetails .= "S/100: {$data['bill_100']} | ";
        $denominationDetails .= "S/200: {$data['bill_200']}\n";
        $denominationDetails .= "Monedas: ";
        $denominationDetails .= "S/0.10: {$data['coin_010']} | ";
        $denominationDetails .= "S/0.20: {$data['coin_020']} | ";
        $denominationDetails .= "S/0.50: {$data['coin_050']} | ";
        $denominationDetails .= "S/1: {$data['coin_1']} | ";
        $denominationDetails .= "S/2: {$data['coin_2']} | ";
        $denominationDetails .= "S/5: {$data['coin_5']}\n";
        $denominationDetails .= "Total contado: S/ " . number_format($totalCounted, 2) . "\n";

        if (!empty($data['closing_observations'])) {
            $denominationDetails .= "Observaciones: {$data['closing_observations']}\n";
        }

        // Añadir a las observaciones existentes
        if (!empty($this->record->observations)) {
            $data['observations'] = $this->record->observations . "\n\n" . $denominationDetails;
        } else {
            $data['observations'] = $denominationDetails;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Verificar si la caja está abierta
        if (!$this->record->is_active) {
            Notification::make()
                ->danger()
                ->title('Error al cerrar caja')
                ->body('Esta caja ya está cerrada.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Verificar si el usuario tiene permiso para cerrar cajas
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['cashier', 'admin', 'super_admin', 'manager'])) {
            Notification::make()
                ->danger()
                ->title('Error al cerrar caja')
                ->body('No tienes permiso para cerrar cajas.')
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Registrar en el log
        \Illuminate\Support\Facades\Log::info('Cierre de caja iniciado', [
            'cash_register_id' => $this->record->id,
            'user_id' => Auth::id(),
            'user_name' => $user->name,
        ]);
    }

    protected function getSavedNotification(): ?Notification
    {
        $isSupervisor = Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']);
        $difference = $this->record->difference ?? 0;
        $expectedAmount = $this->record->expected_amount ?? 0;

        // Verificar si hay una diferencia significativa (más de 50 soles o más del 5%)
        $significantDifference = abs($difference) > 50 ||
                               ($expectedAmount > 0 && abs($difference) / $expectedAmount > 0.05);

        // Base de la notificación
        $notification = Notification::make();

        if ($significantDifference) {
            // Si hay diferencia significativa, mostrar como advertencia
            $notification->warning()
                ->title('Caja cerrada con diferencia significativa');

            // Enviar notificación a supervisores (simulado con log)
            \Illuminate\Support\Facades\Log::warning('Cierre de caja con diferencia significativa', [
                'cash_register_id' => $this->record->id,
                'difference' => $difference,
                'expected' => $expectedAmount,
                'actual' => $this->record->actual_amount,
                'closed_by' => Auth::id(),
                'closed_by_name' => Auth::user()->name,
            ]);
        } else {
            // Si no hay diferencia significativa, mostrar como éxito
            $notification->success()
                ->title('Caja cerrada correctamente');
        }

        // Contenido del mensaje según el rol
        if ($isSupervisor) {
            // Para supervisores, mostrar información detallada
            if ($difference < 0) {
                $notification->body("La caja ha sido cerrada con un faltante de S/ " . number_format(abs($difference), 2));
            } elseif ($difference > 0) {
                $notification->body("La caja ha sido cerrada con un sobrante de S/ " . number_format($difference, 2));
            } else {
                $notification->body("La caja ha sido cerrada sin diferencias.");
            }

            // Si hay diferencia significativa, añadir instrucción
            if ($significantDifference) {
                $notification->body($notification->getBody() . " Se requiere revisión detallada.");
            }
        } else {
            // Para cajeros, solo mostrar confirmación
            $notification->body("La caja ha sido cerrada. Un supervisor revisará el cierre.");
        }

        return $notification;
    }

    public function getHeading(): string
    {
        return 'Cerrar Caja';
    }
}
