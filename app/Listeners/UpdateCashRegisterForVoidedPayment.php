<?php

namespace App\Listeners;

use App\Events\PaymentVoided;
use App\Models\CashRegister;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateCashRegisterForVoidedPayment
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
    public function handle(PaymentVoided $event): void
    {
        $payment = $event->payment;

        // Si el pago no está asociado a una caja, no hay nada que hacer
        if (!$payment->cash_register_id) {
            Log::info('Pago anulado sin caja asociada', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount
            ]);
            return;
        }

        // Obtener la caja asociada
        $cashRegister = CashRegister::find($payment->cash_register_id);

        // Si la caja no existe, registrar y salir
        if (!$cashRegister) {
            Log::warning('Intento de actualizar una caja inexistente para un pago anulado', [
                'payment_id' => $payment->id,
                'cash_register_id' => $payment->cash_register_id
            ]);
            return;
        }

        // Actualizar los totales según el método de pago (restar el monto)
        if ($payment->payment_method === Payment::METHOD_CASH) {
            $cashRegister->cash_sales -= $payment->amount;
        } elseif (in_array($payment->payment_method, [Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD], true)) {
            $cashRegister->card_sales -= $payment->amount;
        } else {
            $cashRegister->other_sales -= $payment->amount;
        }

        // Asegurar que los valores no sean negativos
        $cashRegister->cash_sales = max(0, $cashRegister->cash_sales);
        $cashRegister->card_sales = max(0, $cashRegister->card_sales);
        $cashRegister->other_sales = max(0, $cashRegister->other_sales);

        $cashRegister->total_sales = $cashRegister->cash_sales + $cashRegister->card_sales + $cashRegister->other_sales;
        $cashRegister->save();

        Log::info('Totales de caja actualizados por anulación de pago', [
            'cash_register_id' => $cashRegister->id,
            'payment_id' => $payment->id,
            'payment_method' => $payment->payment_method,
            'amount' => $payment->amount,
            'new_total' => $cashRegister->total_sales
        ]);
    }
}
