<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Redirige al formulario unificado de pago y facturación.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showPaymentForm($orderId)
    {
        // Redirigir al nuevo flujo unificado
        return redirect()->route('pos.unified.form', ['order' => $orderId]);
    }

    /**
     * Redirige al formulario unificado de pago y facturación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request, $orderId)
    {
        // Redirigir al nuevo flujo unificado
        return redirect()->route('pos.unified.form', ['order' => $orderId]);
    }

    /**
     * Muestra el historial de pagos de una orden.
     *
     * @param  int  $orderId
     * @return \Illuminate\View\View
     */
    public function showPaymentHistory($orderId)
    {
        $order = Order::with(['payments' => function($query) {
            $query->orderBy('payment_datetime', 'desc');
        }])->findOrFail($orderId);

        return view('pos.payment-history', [
            'order' => $order,
            'payments' => $order->payments
        ]);
    }

    /**
     * Anula un pago.
     *
     * @param  int  $paymentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voidPayment($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        $orderId = $payment->order_id;

        try {
            // Verificar si el usuario tiene permisos para anular pagos
            if (!Auth::user()->can('void_payments')) {
                return redirect()->route('pos.payment.history', ['order' => $orderId])
                    ->with('error', 'No tiene permisos para anular pagos.');
            }

            // Anular el pago
            $payment->void_reason = 'Anulado por ' . Auth::user()->name;
            // Nota: Los campos voided_at y voided_by no existen en la tabla
            // $payment->voided_at = now();
            // $payment->voided_by = Auth::id();
            $payment->save();

            // Actualizar la caja según el método de pago
            if ($payment->cash_register_id) {
                $cashRegister = CashRegister::find($payment->cash_register_id);
                if ($cashRegister && $cashRegister->is_active) {
                    // Restar el monto según el método de pago
                    if ($payment->payment_method === Payment::METHOD_CASH) {
                        $cashRegister->cash_sales -= $payment->amount;
                    } elseif ($payment->payment_method === Payment::METHOD_CARD) {
                        $cashRegister->card_sales -= $payment->amount;
                    } else {
                        $cashRegister->other_sales -= $payment->amount;
                    }

                    // Actualizar el total de ventas
                    $cashRegister->total_sales = $cashRegister->cash_sales + $cashRegister->card_sales + $cashRegister->other_sales;
                    $cashRegister->save();

                    Log::info('Caja actualizada por anulación de pago', [
                        'payment_id' => $payment->id,
                        'cash_register_id' => $cashRegister->id,
                        'amount' => $payment->amount,
                        'payment_method' => $payment->payment_method
                    ]);
                }
            }

            return redirect()->route('pos.payment.history', ['order' => $orderId])
                ->with('success', 'Pago anulado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al anular el pago', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId
            ]);

            return redirect()->route('pos.payment.history', ['order' => $orderId])
                ->with('error', 'Error al anular el pago: ' . $e->getMessage());
        }
    }
}
