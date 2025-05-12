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
            Log::info('Pago sin caja asociada', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount
            ]);
            return;
        }
        
        // Obtener la caja asociada
        $cashRegister = CashRegister::find($payment->cash_register_id);
        
        // Si la caja no existe o está cerrada, registrar y salir
        if (!$cashRegister || !$cashRegister->is_active) {
            Log::warning('Intento de actualizar una caja inexistente o cerrada', [
                'payment_id' => $payment->id,
                'cash_register_id' => $payment->cash_register_id,
                'exists' => $cashRegister ? 'Sí' : 'No',
                'is_active' => $cashRegister ? $cashRegister->is_active : 'N/A'
            ]);
            return;
        }
        
        // Actualizar los totales según el método de pago
        // Nota: Este código es redundante con el método registerSale, pero lo mantenemos
        // para asegurar que los totales se actualicen correctamente incluso si se registra
        // un pago sin pasar por el método registerPayment
        if ($payment->payment_method === Payment::METHOD_CASH) {
            $cashRegister->cash_sales += $payment->amount;
        } elseif (in_array($payment->payment_method, [Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD])) {
            $cashRegister->card_sales += $payment->amount;
        } else {
            $cashRegister->other_sales += $payment->amount;
        }
        
        $cashRegister->total_sales = $cashRegister->cash_sales + $cashRegister->card_sales + $cashRegister->other_sales;
        $cashRegister->save();
        
        Log::info('Totales de caja actualizados', [
            'cash_register_id' => $cashRegister->id,
            'payment_id' => $payment->id,
            'payment_method' => $payment->payment_method,
            'amount' => $payment->amount,
            'new_total' => $cashRegister->total_sales
        ]);
    }
}
