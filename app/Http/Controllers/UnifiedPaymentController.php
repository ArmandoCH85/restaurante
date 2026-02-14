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
        $genericCustomer = Customer::getGenericCustomer();

        // Obtener las series configuradas desde DocumentSeries
        $salesNoteSeries = \App\Models\DocumentSeries::where('document_type', 'sales_note')->where('active', true)->first();
        $receiptSeries = \App\Models\DocumentSeries::where('document_type', 'receipt')->where('active', true)->first();
        $invoiceSeries = \App\Models\DocumentSeries::where('document_type', 'invoice')->where('active', true)->first();

        // Obtener información de los próximos correlativos
        $nextNumbers = [
            'invoice' => $invoiceSeries ? $invoiceSeries->series . '-' . str_pad($invoiceSeries->current_number, 8, '0', STR_PAD_LEFT) : 'F001-00000001',
            'receipt' => $receiptSeries ? $receiptSeries->series . '-' . str_pad($receiptSeries->current_number, 8, '0', STR_PAD_LEFT) : 'B001-00000001',
            'sales_note' => $salesNoteSeries ? $salesNoteSeries->series . '-' . str_pad($salesNoteSeries->current_number, 8, '0', STR_PAD_LEFT) : 'NV001-00000001',
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
        // Verificar que solo cashiers puedan generar comprobantes
        if (!Auth::user()->hasRole(['cashier', 'admin', 'super_admin'])) {
            return redirect()->route('pos.unified.form', ['order' => $orderId])
                ->with('error', 'No tienes permisos para generar comprobantes. Solo los cajeros pueden realizar esta acción.');
        }

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
            'table_type' => $order->table ? gettype($order->table) : 'NULL',
            'received_customer_id' => $request->customer_id,
            'invoice_type' => $request->invoice_type
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

            // Validar exceso de pago - solo permitir vuelto en pagos de efectivo
            $totalPaid = $order->getTotalPaid();
            $changeAmount = 0;

            if ($totalPaid > $order->total) {
                $changeAmount = $totalPaid - $order->total;

                // Verificar si hay pagos en efectivo para justificar el exceso
                $hasCashPayment = $order->payments()
                    ->where('payment_method', 'cash')
                    ->exists();

                if (!$hasCashPayment) {
                    return redirect()->route('pos.unified.form', ['order' => $order->id])
                        ->with('error', 'El total pagado (S/ ' . number_format($totalPaid, 2) . ') excede el total de la orden (S/ ' . number_format($order->total, 2) . '). Solo se permite exceso en pagos de efectivo como vuelto.');
                }
            }

            // 2. Recalcular totales según el tipo de comprobante
            $this->recalculateOrderTotalsForInvoiceType($order, $request->invoice_type);

            // 3. Validar y preparar el customer_id
            $customerId = $request->customer_id;

            // Verificar que el customer_id sea válido antes de generar el comprobante
            $customer = Customer::find($customerId);
            if (!$customer) {
                // Si el customer_id no es válido, usar el cliente genérico
                $customer = Customer::getGenericCustomer();
                $customerId = $customer->id;

                Log::warning('Customer ID inválido, usando cliente genérico', [
                    'original_customer_id' => $request->customer_id,
                    'generic_customer_id' => $customerId,
                    'order_id' => $order->id
                ]);
            }

            // 4. Generar el comprobante
            // Obtener la serie desde DocumentSeries
            $series = $this->getNextSeries($request->invoice_type);

            // ✅ CRUCIAL: Refrescar relación de pagos justo antes de generar factura
            $order->load('payments');

            Log::info('🔍 UnifiedPaymentController - Payments antes de generateInvoice', [
                'order_id' => $order->id,
                'payments_count' => $order->payments->count(),
                'payments' => $order->payments->map(fn($p) => [
                    'method' => $p->payment_method,
                    'amount' => $p->amount,
                    'created_at' => $p->created_at
                ])->toArray()
            ]);

            $invoice = $order->generateInvoice(
                $request->invoice_type,
                $series,
                $customerId
            );

            if (!$invoice) {
                return redirect()->route('pos.unified.form', ['order' => $order->id])
                    ->with('error', 'No se pudo generar el comprobante. Verifique que la orden esté pagada y no facturada.');
            }

            // 4. Generar la pre-cuenta automáticamente después del comprobante
            // Esto asegura que siempre se genere una pre-cuenta cuando se crea un comprobante

            // 5. Liberar la mesa inmediatamente después de generar el comprobante
            if ($order->table_id) {
                $table = \App\Models\Table::find($order->table_id);
                if ($table) {
                    $table->status = \App\Models\Table::STATUS_AVAILABLE;
                    $table->occupied_at = null;
                    $table->save();

                    \Illuminate\Support\Facades\Log::info('✅ Mesa liberada automáticamente al generar comprobante', [
                        'table_id' => $table->id,
                        'table_number' => $table->number,
                        'invoice_id' => $invoice->id,
                        'order_id' => $order->id
                    ]);
                }
            }

            // 6. Redirigir a la impresión del comprobante con información adicional
            return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                ->with('success', 'Pago registrado y comprobante generado correctamente.')
                ->with('change_amount', $changeAmount)
                ->with('order_id', $order->id)
                ->with('generate_prebill', true) // Flag para indicar que se debe generar pre-cuenta
                ->with('clear_cart_after_print', true) // Flag para limpiar carrito después de imprimir
                ->with('table_id', $order->table_id); // ID de la mesa para limpiar carrito

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
     * Obtener la serie según el tipo de comprobante
     */
    private function getNextSeries($type)
    {
        // Buscar la primera serie activa para este tipo de documento
        $series = \App\Models\DocumentSeries::where('document_type', $type)
            ->where('active', true)
            ->first();

        // Si no se encuentra una serie, usar valores por defecto
        if (!$series) {
            return match ($type) {
                'sales_note' => 'NV001',
                'receipt' => 'B001',
                'invoice' => 'F001',
                'credit_note' => 'FC01',
                default => 'NV001',
            };
        }

        return $series->series;
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
        return $lastInvoice ? (int) $lastInvoice->number + 1 : 1;
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

        // CORRECCIÓN: Los precios YA INCLUYEN IGV
        // El subtotal de la orden ya incluye IGV, necesitamos calcular el desglose
        $totalWithIgvAfterDiscount = $subtotalAfterDiscount;

        // Calcular desglose según el tipo de comprobante
        $tax = 0;
        if (in_array($invoiceType, ['receipt', 'invoice'])) {
            // Calcular IGV incluido en el precio usando métodos del modelo Order (Trait CalculatesIgv)
            $tax = $order->calculateIncludedIgv($totalWithIgvAfterDiscount);
            $subtotalWithoutIgv = $order->calculateSubtotalFromPriceWithIgv($totalWithIgvAfterDiscount);
            $subtotalAfterDiscount = $subtotalWithoutIgv; // Actualizar para BD
        }
        // Las notas de venta no tienen IGV

        // El total es el precio con IGV (no se agrega IGV adicional)
        $total = $totalWithIgvAfterDiscount;

        // Actualizar los valores en la orden
        $order->subtotal = $subtotal;
        $order->tax = $tax;
        $order->total = $total;
        $order->save();

        // ✅ CRUCIAL: Refrescar relación de pagos después del save
        $order->load('payments');

        // Log para depuración
        Log::info('Totales recalculados para facturación', [
            'order_id' => $order->id,
            'invoice_type' => $invoiceType,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'tax' => $tax,
            'total' => $total,
            'payments_count' => $order->payments->count() // ✅ Verificar que los pagos siguen ahí
        ]);
    }
}
