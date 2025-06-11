<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoicePdfController extends Controller
{
    /**
     * Generar y mostrar el PDF de un comprobante
     */
    public function show(Invoice $invoice)
    {
        // Log de acceso al controlador
        Log::info('🖨️ ACCESO AL CONTROLADOR DE IMPRESIÓN [' . \Illuminate\Support\Str::random(8) . ']', [
            'invoice_id' => $invoice->id,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'timestamp' => now()->format('Y-m-d H:i:s.u')
        ]);

        // Cargar relaciones necesarias
        $invoice->load(['customer', 'details.product', 'order.table', 'employee']);

        // Log de inicio del proceso
        Log::info('🔄 Iniciando impresión de comprobante', [
            'invoice_id' => $invoice->id,
            'invoice_type' => $invoice->invoice_type,
            'series' => $invoice->series,
            'number' => $invoice->number,
            'url' => request()->fullUrl(),
            'referer' => request()->header('Referer'),
            'user_id' => auth()->id() ?? 'guest',
            'user_name' => auth()->user()?->name ?? 'guest',
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent')
        ]);

        // Determinar la vista según el tipo de comprobante
        $view = match($invoice->invoice_type) {
            'receipt' => 'pdf.receipt',
            'sales_note' => 'pdf.sales_note',
            default => 'pdf.invoice'
        };

        Log::info('🖨️ Procesando impresión de comprobante', [
            'invoice_id' => $invoice->id,
            'type' => $invoice->invoice_type,
            'series' => $invoice->series,
            'number' => $invoice->number
        ]);

        // Obtener la referencia al cliente para usarla en la vista
        $customer = $invoice->customer;

        // ✅ VERIFICACIÓN ADICIONAL: Si no hay customer, usar cliente genérico
        if (!$customer) {
            $customer = Customer::getGenericCustomer();
            Log::warning('🔄 Cliente no encontrado para factura, usando cliente genérico', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id
            ]);
        }

        // Calcular el cambio si es necesario
        $changeAmount = 0;
        if ($invoice->payment_amount && $invoice->payment_amount > $invoice->total) {
            $changeAmount = $invoice->payment_amount - $invoice->total;
        }

        // Datos para la vista
        $viewData = [
            'invoice' => $invoice,
            'customer' => $customer,
            'change_amount' => $changeAmount,
            'qr_code' => $invoice->qr_code ?? null,
        ];

        Log::info('🖨️ COMPROBANTE LISTO PARA IMPRESIÓN', [
            'invoice_id' => $invoice->id,
            'invoice_type' => $invoice->invoice_type,
            'view_template' => $view,
            'customer' => $customer->name ?? 'N/A',
            'total' => $invoice->total,
            'status' => $invoice->tax_authority_status ?? 'N/A',
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'process_time_ms' => (now()->format('U.u') - request()->server('REQUEST_TIME_FLOAT')) * 1000
        ]);

        // Retornar la vista
        return view($view, $viewData);
    }

    /**
     * Generar y descargar el PDF de un comprobante
     */
    public function download(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load(['customer', 'details.product', 'order.table', 'employee']);

        // Determinar la vista según el tipo de comprobante
        $view = match($invoice->invoice_type) {
            'receipt' => 'pdf.receipt',
            'sales_note' => 'pdf.sales_note',
            default => 'pdf.invoice'
        };

        // Obtener la referencia al cliente
        $customer = $invoice->customer;
        if (!$customer) {
            $customer = Customer::getGenericCustomer();
        }

        // Calcular el cambio si es necesario
        $changeAmount = 0;
        if ($invoice->payment_amount && $invoice->payment_amount > $invoice->total) {
            $changeAmount = $invoice->payment_amount - $invoice->total;
        }

        // Generar PDF
        $pdf = Pdf::loadView($view, [
            'invoice' => $invoice,
            'customer' => $customer,
            'change_amount' => $changeAmount,
            'qr_code' => $invoice->qr_code ?? null,
        ]);

        // Configurar el PDF
        $pdf->setPaper('a4');

        // Nombre del archivo
        $filename = match($invoice->invoice_type) {
            'receipt' => 'boleta_',
            'sales_note' => 'nota_venta_',
            default => 'factura_'
        } . $invoice->series . '-' . $invoice->number . '.pdf';

        // Descargar el PDF
        return $pdf->download($filename);
    }
}
