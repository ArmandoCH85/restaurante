<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegisterExpense;
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
        // Calcular el total de efectivo contado (billetes y monedas)
        $totalCashCounted = 0;

        // Billetes
        $totalCashCounted += ($data['bill_10'] ?? 0) * 10;
        $totalCashCounted += ($data['bill_20'] ?? 0) * 20;
        $totalCashCounted += ($data['bill_50'] ?? 0) * 50;
        $totalCashCounted += ($data['bill_100'] ?? 0) * 100;
        $totalCashCounted += ($data['bill_200'] ?? 0) * 200;

        // Monedas
        $totalCashCounted += ($data['coin_010'] ?? 0) * 0.1;
        $totalCashCounted += ($data['coin_020'] ?? 0) * 0.2;
        $totalCashCounted += ($data['coin_050'] ?? 0) * 0.5;
        $totalCashCounted += ($data['coin_1'] ?? 0) * 1;
        $totalCashCounted += ($data['coin_2'] ?? 0) * 2;
        $totalCashCounted += ($data['coin_5'] ?? 0) * 5;

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

        // NUEVA FÓRMULA (Ajuste Usuario): Diferencia = (Total Contado + Apertura - Egresos) - Esperado
        // Donde Total Contado representa las Ventas Brutas Reportadas por el Cajero
        $totalCalculadoCajero = $totalCounted + $this->record->opening_amount - $totalExpenses;
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

        // Guardar el desglose completo en las observaciones
        $denominationDetails = "=== CIERRE DE CAJA - RESUMEN COMPLETO ===\n\n";

        // Información del cierre
        $denominationDetails .= "TOTAL INGRESOS: S/ " . number_format($totalIngresos, 2) . "\n";
        $denominationDetails .= "TOTAL EGRESOS: S/ " . number_format($totalExpenses, 2) . "\n";
        $denominationDetails .= "GANANCIA REAL: S/ " . number_format($gananciaReal, 2) . "\n";
        $denominationDetails .= "   (Ingresos - Egresos)\n\n";

        $denominationDetails .= "MONTO ESPERADO: S/ " . number_format($expectedAmount, 2) . "\n";
        $denominationDetails .= "   (Monto inicial: S/ " . number_format($this->record->opening_amount, 2);
        $denominationDetails .= " + Ventas del día: S/ " . number_format($totalIngresos, 2) . ")\n\n";

        // Efectivo contado
        $denominationDetails .= "EFECTIVO CONTADO: S/ " . number_format($totalCashCounted, 2) . "\n";
        $denominationDetails .= "Billetes: ";
        $denominationDetails .= "S/200×" . ($data['bill_200'] ?? 0) . " | S/100×" . ($data['bill_100'] ?? 0) . " | S/50×" . ($data['bill_50'] ?? 0) . " | ";
        $denominationDetails .= "S/20×" . ($data['bill_20'] ?? 0) . " | S/10×" . ($data['bill_10'] ?? 0) . "\n";
        $denominationDetails .= "Monedas: ";
        $denominationDetails .= "S/5×" . ($data['coin_5'] ?? 0) . " | S/2×" . ($data['coin_2'] ?? 0) . " | S/1×" . ($data['coin_1'] ?? 0) . " | ";
        $denominationDetails .= "S/0.50×" . ($data['coin_050'] ?? 0) . " | S/0.20×" . ($data['coin_020'] ?? 0) . " | S/0.10×" . ($data['coin_010'] ?? 0) . "\n\n";

        // Otros métodos de pago
        if ($otherPaymentsCounted > 0) {
            $denominationDetails .= "OTROS METODOS DE PAGO: S/ " . number_format($otherPaymentsCounted, 2) . "\n";
            if (($data['manual_yape'] ?? 0) > 0)
                $denominationDetails .= "Yape: S/ " . number_format($data['manual_yape'], 2) . " | ";
            if (($data['manual_plin'] ?? 0) > 0)
                $denominationDetails .= "Plin: S/ " . number_format($data['manual_plin'], 2) . " | ";
            if (($data['manual_card'] ?? 0) > 0)
                $denominationDetails .= "Tarjeta: S/ " . number_format($data['manual_card'], 2) . " | ";
            if (($data['manual_didi'] ?? 0) > 0)
                $denominationDetails .= "Didi: S/ " . number_format($data['manual_didi'], 2) . " | ";
            if (($data['manual_pedidos_ya'] ?? 0) > 0)
                $denominationDetails .= "Pedidos Ya: S/ " . number_format($data['manual_pedidos_ya'], 2) . " | ";
            if (($data['manual_bita_express'] ?? 0) > 0) {
                $denominationDetails .= "Bita Express: S/ " . number_format($data['manual_bita_express'], 2) . " | ";
            }
            if (($data['manual_otros'] ?? 0) > 0) {
                $denominationDetails .= "Otros: S/ " . number_format($data['manual_otros'], 2) . " | ";
            }
            $denominationDetails .= "\n\n";
        }

        // Desglose de egresos (ahora se obtienen del módulo separado)
        if ($totalExpenses > 0) {
            $denominationDetails .= "EGRESOS REGISTRADOS (desde modulo de Egresos):\n";
            $denominationDetails .= "  Total: S/ " . number_format($totalExpenses, 2) . "\n";
            $denominationDetails .= "  Ver detalles en: /admin/egresos\n\n";
        }

        // Totales finales
        $denominationDetails .= "TOTAL MANUAL (Ventas): S/ " . number_format($totalCounted, 2) . "\n";
        $denominationDetails .= "DIFERENCIA: S/ " . number_format($difference, 2);
        if ($difference > 0) {
            $denominationDetails .= " (SOBRANTE)\n";
        } elseif ($difference < 0) {
            $denominationDetails .= " (FALTANTE)\n";
        } else {
            $denominationDetails .= " (SIN DIFERENCIA)\n";
        }
        $denominationDetails .= "   Fórmula: (Manual + Inicial - Egresos) - Esperado\n";
        $denominationDetails .= "\n";

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
                $notification->body("La caja ha sido cerrada con un SOBRANTE de S/ " . number_format($difference, 2));
            } elseif ($difference < 0) {
                $notification->body("La caja ha sido cerrada con un FALTANTE de S/ " . number_format(abs($difference), 2));
            } else {
                $notification->body("La caja ha sido cerrada sin diferencias. Total Ventas: S/" . number_format($this->record->actual_amount, 2));
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
        return 'Cerrar Operación de Caja';
    }
}
