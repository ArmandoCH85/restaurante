<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Support\CashRegisterClosingSummaryService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

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
        // Total alternativo por denominaciones para mantener compatibilidad.
        $denominationsCashTotal = 0;
        $denominationsCashTotal += ($data['bill_10'] ?? 0) * 10;
        $denominationsCashTotal += ($data['bill_20'] ?? 0) * 20;
        $denominationsCashTotal += ($data['bill_50'] ?? 0) * 50;
        $denominationsCashTotal += ($data['bill_100'] ?? 0) * 100;
        $denominationsCashTotal += ($data['bill_200'] ?? 0) * 200;
        $denominationsCashTotal += ($data['coin_010'] ?? 0) * 0.1;
        $denominationsCashTotal += ($data['coin_020'] ?? 0) * 0.2;
        $denominationsCashTotal += ($data['coin_050'] ?? 0) * 0.5;
        $denominationsCashTotal += ($data['coin_1'] ?? 0) * 1;
        $denominationsCashTotal += ($data['coin_2'] ?? 0) * 2;
        $denominationsCashTotal += ($data['coin_5'] ?? 0) * 5;

        // manual_cash es la fuente principal del flujo actual.
        $manualCashInput = null;
        if (array_key_exists('manual_cash', $data) && $data['manual_cash'] !== null && $data['manual_cash'] !== '') {
            $manualCashInput = (float) $data['manual_cash'];
        }

        // Alerta y bloqueo: hay efectivo en sistema pero no se registró efectivo contado.
        $systemCashSales = (float) $this->record->getSystemCashSales();
        if (
            $systemCashSales > 0.009 &&
            (($manualCashInput ?? 0.0) <= 0.009) &&
            $denominationsCashTotal <= 0.009
        ) {
            Notification::make()
                ->warning()
                ->title('Falta registrar el efectivo contado')
                ->body('El sistema registra S/ '.number_format($systemCashSales, 2).' en efectivo. Ingrese `manual_cash` para continuar.')
                ->send();

            throw ValidationException::withMessages([
                'manual_cash' => 'Debe ingresar el efectivo contado. El sistema registra S/ '.number_format($systemCashSales, 2).' en efectivo.',
            ]);
        }

        // Priorizar manual_cash; si no hay dato útil, usar denominaciones.
        $totalCashCounted = ($manualCashInput !== null && $manualCashInput > 0)
            ? $manualCashInput
            : $denominationsCashTotal;

        // Calcular total de otros métodos de pago
        $otherPaymentsCounted = ($data['manual_yape'] ?? 0) +
            ($data['manual_plin'] ?? 0) +
            ($data['manual_card'] ?? 0) +
            ($data['manual_didi'] ?? 0) +
            ($data['manual_pedidos_ya'] ?? 0) +
            ($data['manual_bita_express'] ?? 0) +
            ($data['manual_otros'] ?? 0);

        // Total contado = efectivo + otros métodos
        $totalCounted = $totalCashCounted + $otherPaymentsCounted;

        // Los egresos ahora se registran en el módulo separado, no se procesan aquí
        // El total se obtiene directamente desde la relación cashRegisterExpenses
        $totalExpenses = $this->record->cashRegisterExpenses()->sum('amount');
        $data['total_expenses'] = $totalExpenses;

        // Monto esperado consistente con el modelo
        $expectedAmount = $this->record->calculateExpectedCash();

        // Diferencia de cierre: (Total Contado + Apertura) - Esperado.
        // Nota: el monto esperado ya descuenta egresos.
        $totalCalculadoCajero = $totalCounted + $this->record->opening_amount;
        $difference = $totalCalculadoCajero - $expectedAmount;

        // Añadir datos para el cierre de caja
        $data['closed_by'] = Auth::id();
        $data['closing_datetime'] = now();
        $data['is_active'] = false;
        $data['actual_amount'] = $totalCounted;
        $data['expected_amount'] = $expectedAmount;
        $data['difference'] = $difference;

        // Calcular ganancia real (Ingresos - Egresos)
        $totalIngresos = $this->record->getSystemTotalSales();
        $gananciaReal = $totalIngresos - $totalExpenses;

        $summaryService = app(CashRegisterClosingSummaryService::class);
        $summary = $summaryService->build([
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalExpenses,
            'ganancia_real' => $gananciaReal,
            'monto_inicial' => (float) $this->record->opening_amount,
            'monto_esperado' => $expectedAmount,
            'efectivo_total' => $totalCashCounted,
            'total_manual_ventas' => $totalCounted,
            'difference' => $difference,
            'billetes' => [
                '200' => (int) ($data['bill_200'] ?? 0),
                '100' => (int) ($data['bill_100'] ?? 0),
                '50' => (int) ($data['bill_50'] ?? 0),
                '20' => (int) ($data['bill_20'] ?? 0),
                '10' => (int) ($data['bill_10'] ?? 0),
            ],
            'monedas' => [
                '5' => (int) ($data['coin_5'] ?? 0),
                '2' => (int) ($data['coin_2'] ?? 0),
                '1' => (int) ($data['coin_1'] ?? 0),
                '0.50' => (int) ($data['coin_050'] ?? 0),
                '0.20' => (int) ($data['coin_020'] ?? 0),
                '0.10' => (int) ($data['coin_010'] ?? 0),
            ],
            'otros_metodos' => [
                'yape' => (float) ($data['manual_yape'] ?? 0),
                'plin' => (float) ($data['manual_plin'] ?? 0),
                'tarjeta' => (float) ($data['manual_card'] ?? 0),
                'didi' => (float) ($data['manual_didi'] ?? 0),
                'pedidos_ya' => (float) ($data['manual_pedidos_ya'] ?? 0),
                'bita_express' => (float) ($data['manual_bita_express'] ?? 0),
                'otros' => (float) ($data['manual_otros'] ?? 0),
            ],
            'closed_by' => Auth::id(),
            'closing_datetime' => now()->toDateTimeString(),
            'closing_observations' => $data['closing_observations'] ?? null,
        ]);

        if (Schema::hasColumn('cash_registers', 'closing_summary_json')) {
            $data['closing_summary_json'] = $summary;
        }
        $denominationDetails = $summaryService->toLegacyText($summary);

        // Añadir a las observaciones existentes
        if (! empty($this->record->observations)) {
            $data['observations'] = $this->record->observations."\n\n".$denominationDetails;
        } else {
            $data['observations'] = $denominationDetails;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // Verificar si la caja está abierta
        if (! $this->record->is_active) {
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
        if (! $user || ! $user->hasAnyRole(['cashier', 'admin', 'super_admin', 'manager'])) {
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

    protected function afterSave(): void
    {
        // Los egresos ahora se registran en el módulo separado
        // No se procesan egresos desde el formulario de cierre de caja
    }

    protected function getSavedNotification(): ?Notification
    {
        $isSupervisor = Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier']);
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
                ->title('Caja cerrada con diferencia significativa')
                ->duration(8000);

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
                ->title('Caja cerrada exitosamente')
                ->duration(5000);
        }

        // Contenido del mensaje según el rol
        if ($isSupervisor) {
            // Para supervisores, mostrar información detallada
            if ($difference > 0) {
                $notification->body('La caja ha sido cerrada con un SOBRANTE de S/ '.number_format($difference, 2));
            } elseif ($difference < 0) {
                $notification->body('La caja ha sido cerrada con un FALTANTE de S/ '.number_format(abs($difference), 2));
            } else {
                $notification->body('La caja ha sido cerrada sin diferencias. Total Ventas: S/'.number_format($this->record->actual_amount, 2));
            }

            // Si hay diferencia significativa, añadir instrucción
            if ($significantDifference) {
                $notification->body($notification->getBody().' Se requiere revisión detallada.');
            }
        } else {
            // Para cajeros, solo mostrar confirmación
            $notification->body('La caja ha sido cerrada. Un supervisor revisará el cierre.');
        }

        return $notification;
    }

    public function getHeading(): string
    {
        return 'Cerrar Operación de Caja';
    }
}
