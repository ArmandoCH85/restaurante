<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

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
        $invoice = Invoice::with([
            'order.orderDetails.product',
            'order.table',
            'order.deliveryOrder', // Agregar relación de delivery
            'customer',
            'details'
        ])->findOrFail($invoiceId);

        // Obtener el cambio/vuelto de la sesión o calcularlo
        $changeAmount = session('change_amount', 0);

        // Si no hay cambio en la sesión, calcularlo basado en los datos de la factura
        if ($changeAmount == 0 && $invoice->payment_method === 'cash' && $invoice->payment_amount > $invoice->total) {
            $changeAmount = $invoice->payment_amount - $invoice->total;
        }

        // Verificar si se debe generar automáticamente la pre-cuenta
        $generatePreBill = session('generate_prebill', false);
        $orderId = session('order_id', null);
        $preBillUrl = null;

        if ($generatePreBill && $orderId && $invoice->order) {
            // Generar la URL de la pre-cuenta para abrir automáticamente
            $preBillUrl = route('pos.prebill.pdf', ['order' => $invoice->order->id]);
        }

        // Limpiar TODAS las variables de sesión relacionadas con el flujo unificado
        session()->forget([
            'generate_prebill',
            'order_id',
            'clear_cart_after_print',
            'table_id'
        ]);

        // Determinar la vista según el tipo de comprobante
        $view = match($invoice->invoice_type) {
            'receipt' => 'pos.receipt-print',
            'sales_note' => 'pos.sales-note-print',
            default => 'pos.invoice-print'
        };

        return view($view, [
            'invoice' => $invoice,
            'date' => $invoice->issue_date->format('d/m/Y'),
            'change_amount' => $changeAmount,
            'qr_code' => $invoice->qr_code ?? null,
            'prebill_url' => $preBillUrl // URL para generar automáticamente la pre-cuenta
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
     * Vista previa térmica para desarrollo/pruebas.
     *
     * @param  int  $invoiceId
     * @return \Illuminate\View\View
     */
    public function thermalPreview($invoiceId)
    {
        $invoice = \App\Models\Invoice::with([
            'customer',
            'details',
            'order.table',
            'order.deliveryOrder' // Agregar relación de delivery
        ])->findOrFail($invoiceId);

        $date = now()->format('d/m/Y H:i:s');

        // Determinar la vista según el tipo de comprobante
        $view = match($invoice->invoice_type) {
            'receipt' => 'pos.receipt-print',
            'sales_note' => 'pos.sales-note-print',
            default => 'pos.invoice-print'
        };

        return view($view, [
            'invoice' => $invoice,
            'date' => $date,
            'change_amount' => 0, // Para vista previa
            'thermal_preview' => true // Flag para identificar vista previa
        ])->with('thermalPreviewMode', true);
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
     * Descargar XML del comprobante
     */
    public function downloadXml(Invoice $invoice)
    {
        if (!$invoice->xml_path || !File::exists($invoice->xml_path)) {
            abort(404, 'Archivo XML no encontrado');
        }

        $filename = basename($invoice->xml_path);

        return response()->download($invoice->xml_path, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Descargar CDR del comprobante
     */
    public function downloadCdr(Invoice $invoice)
    {
        if (!$invoice->cdr_path || !File::exists($invoice->cdr_path)) {
            abort(404, 'Archivo CDR no encontrado');
        }

        $filename = basename($invoice->cdr_path);

        return response()->download($invoice->cdr_path, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Descargar PDF del comprobante
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Si existe un PDF generado por SUNAT, descargarlo
        if ($invoice->pdf_path && File::exists($invoice->pdf_path)) {
            $filename = basename($invoice->pdf_path);
            return response()->download($invoice->pdf_path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Si no existe PDF de SUNAT, generar uno usando la vista de impresión
        return $this->generatePdf($invoice->id);
    }
}
