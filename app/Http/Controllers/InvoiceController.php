<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Redirige al formulario unificado de pago y facturación.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showInvoiceForm($orderId)
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
    public function generateInvoice(Request $request, $orderId)
    {
        // Redirigir al nuevo flujo unificado
        return redirect()->route('pos.unified.form', ['order' => $orderId]);
    }

    /**
     * Muestra una factura para impresión.
     *
     * @param  int  $invoiceId
     * @return \Illuminate\View\View
     */
    public function printInvoice($invoiceId)
    {
        $invoice = Invoice::with(['order.orderDetails.product', 'order.table', 'customer', 'details'])
            ->findOrFail($invoiceId);

        // Obtener el cambio/vuelto de la sesión o calcularlo
        $changeAmount = session('change_amount', 0);

        // Si no hay cambio en la sesión, calcularlo basado en los datos de la factura
        if ($changeAmount == 0 && $invoice->payment_method === 'cash' && $invoice->payment_amount > $invoice->total) {
            $changeAmount = $invoice->payment_amount - $invoice->total;
        }

        return view('pos.invoice-print', [
            'invoice' => $invoice,
            'date' => $invoice->issue_date->format('d/m/Y'),
            'change_amount' => $changeAmount,
            'qr_code' => $invoice->qr_code ?? null
        ]);
    }

    /**
     * Anula una factura.
     *
     * @param  int  $invoiceId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voidInvoice(Request $request, $invoiceId)
    {
        $request->validate([
            'void_reason' => 'required|string|max:255',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        try {
            // Verificar si la factura puede ser anulada
            if (!$invoice->canBeVoided()) {
                return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                    ->with('error', 'Esta factura no puede ser anulada.');
            }

            // Anular la factura
            $result = $invoice->void($request->void_reason);

            if (!$result) {
                return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                    ->with('error', 'No se pudo anular la factura.');
            }

            // Actualizar la orden
            $order = $invoice->order;
            $order->billed = false;
            $order->save();

            return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                ->with('success', 'Factura anulada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al anular la factura', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId
            ]);

            return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                ->with('error', 'Error al anular la factura: ' . $e->getMessage());
        }
    }

    /**
     * Genera un PDF de la factura.
     *
     * @param  int  $invoiceId
     * @return \Illuminate\View\View
     */
    public function generatePdf($invoiceId)
    {
        // Redirigir a la vista de impresión, que ya tiene la lógica para mostrar la factura
        return $this->printInvoice($invoiceId);
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
}
