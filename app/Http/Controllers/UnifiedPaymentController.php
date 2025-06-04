<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UnifiedPaymentController extends Controller
{
    /**
     * Muestra el formulario unificado de pago y facturación.
     *
     * @param  int  $orderId
     * @return \Illuminate\View\View
     */
    public function showUnifiedForm($orderId)
    {
        $order = Order::with(['orderDetails.product', 'customer', 'payments', 'table', 'deliveryOrder'])
            ->findOrFail($orderId);

        // Verificar si hay una caja abierta
        $activeCashRegister = CashRegister::where('is_active', true)->first();

        // Obtener clientes para el formulario
        $customers = Customer::orderBy('name')->get();

        // Obtener el cliente genérico (para notas de venta)
        $genericCustomer = Customer::where('document_number', '00000000')->first();
        if (!$genericCustomer) {
            $genericCustomer = Customer::first(); // Usar el primer cliente si no hay uno genérico
        }

        // Obtener los próximos números de comprobantes
        $nextNumbers = [
            'sales_note' => 'NV-' . str_pad($this->getNextNumber('sales_note'), 8, '0', STR_PAD_LEFT),
            'receipt' => 'B001-' . str_pad($this->getNextNumber('receipt'), 8, '0', STR_PAD_LEFT),
            'invoice' => 'F001-' . str_pad($this->getNextNumber('invoice'), 8, '0', STR_PAD_LEFT),
        ];

        return view('pos.unified-payment-form', [
            'order' => $order,
            'customers' => $customers,
            'genericCustomer' => $genericCustomer,
            'nextNumbers' => $nextNumbers,
            'activeCashRegister' => $activeCashRegister,
            'totalPaid' => $order->getTotalPaid(),
            'remainingBalance' => $order->getRemainingBalance()
        ]);
    }

    /**
     * Procesa el pago y genera el comprobante en un solo paso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processUnified(Request $request, $orderId)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reference_number' => 'nullable|string|max:255',
            'wallet_type' => 'nullable|string',
            'invoice_type' => 'required|string|in:sales_note,receipt,invoice',
            'customer_id' => 'required|exists:customers,id',
            'payment_count' => 'nullable|integer',
        ]);

        $order = Order::with(['orderDetails.product', 'customer', 'payments', 'table', 'deliveryOrder'])
            ->findOrFail($orderId);

        // Log para debugging de la orden cargada
        Log::info('Order loaded in processUnified', [
            'order_id' => $order->id,
            'service_type' => $order->service_type,
            'table_id' => $order->table_id,
            'has_table_relation' => $order->table ? 'YES' : 'NO',
            'table_type' => $order->table ? gettype($order->table) : 'NULL'
        ]);

        try {
            // 1. Registrar el pago principal
            // Si es una billetera digital, guardar el tipo en el número de referencia
            $referenceNumber = $request->reference_number;
            if ($request->payment_method === 'digital_wallet' && $request->wallet_type) {
                $referenceNumber = ($referenceNumber ? $referenceNumber . ' - ' : '') . 'Tipo: ' . $request->wallet_type;
            }

            $payment = $order->registerPayment(
                $request->payment_method,
                $request->amount,
                $referenceNumber
            );

            // 2. Registrar pagos adicionales si existen
            if ($request->has('payment_count') && $request->payment_count > 0) {
                for ($i = 1; $i <= $request->payment_count; $i++) {
                    $methodKey = 'additional_payment_method_' . $i;
                    $amountKey = 'additional_amount_' . $i;
                    $referenceKey = 'additional_reference_' . $i;
                    $walletTypeKey = 'additional_wallet_type_' . $i;

                    if ($request->has($methodKey) && $request->has($amountKey)) {
                        $additionalMethod = $request->$methodKey;
                        $additionalAmount = $request->$amountKey;
                        $additionalReference = $request->$referenceKey ?? null;

                        // Si es una billetera digital, guardar el tipo en el número de referencia
                        if ($additionalMethod === 'digital_wallet' && $request->has($walletTypeKey)) {
                            $additionalReference = ($additionalReference ? $additionalReference . ' - ' : '') .
                                                  'Tipo: ' . $request->$walletTypeKey;
                        }

                        $order->registerPayment(
                            $additionalMethod,
                            $additionalAmount,
                            $additionalReference
                        );
                    }
                }
            }

            // Verificar si el pago cubre el total de la orden
            if (!$order->isFullyPaid()) {
                return redirect()->route('pos.unified.form', ['order' => $order->id])
                    ->with('error', 'El pago no cubre el total de la orden. Saldo pendiente: ' . number_format($order->getRemainingBalance(), 2));
            }

            // Calcular el cambio/vuelto si el pago fue en efectivo y el monto total pagado es mayor al total de la orden
            $changeAmount = 0;
            $totalPaid = $order->getTotalPaid();
            if ($totalPaid > $order->total) {
                $changeAmount = $totalPaid - $order->total;
            }

            // 2. Recalcular totales según el tipo de comprobante
            $this->recalculateOrderTotalsForInvoiceType($order, $request->invoice_type);

            // 3. Generar el comprobante
            // Determinar la serie según el tipo de comprobante
            //se debe generar de la base de datos
            $series = '';
            switch ($request->invoice_type) {
                case 'sales_note':
                    $series = 'NV';
                    break;
                case 'receipt':
                    $series = 'B001';
                    break;
                case 'invoice':
                    $series = 'F001';
                    break;
            }

            $invoice = $order->generateInvoice(
                $request->invoice_type,
                $series,
                $request->customer_id
            );

            if (!$invoice) {
                return redirect()->route('pos.unified.form', ['order' => $order->id])
                    ->with('error', 'No se pudo generar el comprobante. Verifique que la orden esté pagada y no facturada.');
            }

            // 4. Redirigir a la impresión del comprobante
            return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                ->with('success', 'Pago registrado y comprobante generado correctamente.')
                ->with('change_amount', $changeAmount);

        } catch (\Exception $e) {
            Log::error('Error al procesar el pago y generar comprobante', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'payment_data' => $request->only(['payment_method', 'amount', 'reference_number']),
                'invoice_data' => $request->only(['invoice_type', 'customer_id'])
            ]);

            return redirect()->route('pos.unified.form', ['order' => $order->id])
                ->with('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el siguiente número para un tipo de comprobante.
     *
     * @param string $type Tipo de comprobante (sales_note, receipt, invoice)
     * @return int Siguiente número
     */
    private function getNextNumber(string $type): int
    {
        $lastInvoice = Invoice::where('invoice_type', $type)->latest('number')->first();
        return $lastInvoice ? (int)$lastInvoice->number + 1 : 1;
    }

    /**
     * Recalcula los totales de la orden según el tipo de comprobante.
     *
     * @param Order $order La orden a recalcular
     * @param string $invoiceType Tipo de comprobante (sales_note, receipt, invoice)
     * @return void
     */
    private function recalculateOrderTotalsForInvoiceType(Order $order, string $invoiceType): void
    {
        // Recargar la relación para asegurar datos actualizados
        $order->load('orderDetails');

        // Calcular subtotal base
        $subtotal = $order->orderDetails->sum('subtotal');

        // Aplicar descuento si existe
        $discountAmount = $order->discount ?? 0;
        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // Calcular IGV según el tipo de comprobante
        $tax = 0;
        if (in_array($invoiceType, ['receipt', 'invoice'])) {
            // Solo boletas y facturas tienen IGV (18%)
            $tax = round($subtotalAfterDiscount * 0.18, 2);
        }
        // Las notas de venta no tienen IGV

        // Calcular total
        $total = round($subtotalAfterDiscount + $tax, 2);

        // Actualizar los valores en la orden
        $order->subtotal = $subtotal;
        $order->tax = $tax;
        $order->total = $total;
        $order->save();

        // Log para depuración
        Log::info('Totales recalculados para facturación', [
            'order_id' => $order->id,
            'invoice_type' => $invoiceType,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'tax' => $tax,
            'total' => $total
        ]);
    }
}
