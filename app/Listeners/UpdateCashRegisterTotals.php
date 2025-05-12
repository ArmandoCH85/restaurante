<?php

namespace App\Listeners;

use App\Events\PaymentRegistered;
use App\Models\CashRegister;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateCashRegisterTotals
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentRegistered $event): void
    {
        $payment = $event->payment;

        // Si el pago no está asociado a una caja, no hay nada que hacer
        if (!$payment->cash_register_id) {
            return;
        }

        // Obtener la caja asociada
        $cashRegister = CashRegister::find($payment->cash_register_id);

        // Si la caja no existe o está cerrada, registrar y salir
        if (!$cashRegister || !$cashRegister->is_active) {
            Log::warning('Verificación de integridad: Pago asociado a una caja inexistente o cerrada', [
                'payment_id' => $payment->id,
                'cash_register_id' => $payment->cash_register_id,
                'exists' => $cashRegister ? 'Sí' : 'No',
                'is_active' => $cashRegister ? $cashRegister->is_active : 'N/A'
            ]);
            return;
        }

        // Este listener ahora solo verifica la integridad de los datos
        // No actualiza los totales, ya que eso lo hace CashRegister::registerSale()

        // Verificar si los totales son consistentes
        $expectedTotal = $cashRegister->cash_sales + $cashRegister->card_sales + $cashRegister->other_sales;
        if (abs($cashRegister->total_sales - $expectedTotal) > 0.001) { // Usar tolerancia para comparaciones de punto flotante
            Log::warning('Verificación de integridad: Inconsistencia en los totales de caja', [
                'cash_register_id' => $cashRegister->id,
                'payment_id' => $payment->id,
                'calculated_total' => $expectedTotal,
                'stored_total' => $cashRegister->total_sales,
                'difference' => $cashRegister->total_sales - $expectedTotal
            ]);

            // Corregir la inconsistencia
            \Illuminate\Support\Facades\DB::transaction(function () use ($cashRegister, $expectedTotal) {
                $cashRegister->total_sales = $expectedTotal;
                $cashRegister->save();

                Log::info('Verificación de integridad: Total de caja corregido', [
                    'cash_register_id' => $cashRegister->id,
                    'new_total' => $expectedTotal
                ]);
            });
        }
    }
}
