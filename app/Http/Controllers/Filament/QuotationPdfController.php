<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuotationPdfController extends Controller
{
    /**
     * Generar y mostrar el PDF de una cotización
     */
    public function show(Quotation $quotation)
    {
        // Cargar relaciones necesarias
        $quotation->load('details.product', 'customer', 'user');

        // Generar PDF
        $pdf = Pdf::loadView('reports.quotation', [
            'quotation' => $quotation,
            'details' => $quotation->details,
            'customer' => $quotation->customer,
            'user' => $quotation->user,
        ]);

        // Configurar el PDF
        $pdf->setPaper('a4');

        // Mostrar el PDF en el navegador
        return $pdf->stream('cotizacion_' . $quotation->quotation_number . '.pdf');
    }

    /**
     * Generar y descargar el PDF de una cotización
     */
    public function download(Quotation $quotation)
    {
        // Cargar relaciones necesarias
        $quotation->load('details.product', 'customer', 'user');

        // Generar PDF
        $pdf = Pdf::loadView('reports.quotation', [
            'quotation' => $quotation,
            'details' => $quotation->details,
            'customer' => $quotation->customer,
            'user' => $quotation->user,
        ]);

        // Configurar el PDF
        $pdf->setPaper('a4');

        // Descargar el PDF
        return $pdf->download('cotizacion_' . $quotation->quotation_number . '.pdf');
    }

    /**
     * Enviar la cotización por correo electrónico
     */
    public function email(Request $request, Quotation $quotation)
    {
        // Validar la solicitud
        $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        // Cargar relaciones necesarias
        $quotation->load('details.product', 'customer', 'user');

        // Generar PDF
        $pdf = Pdf::loadView('reports.quotation', [
            'quotation' => $quotation,
            'details' => $quotation->details,
            'customer' => $quotation->customer,
            'user' => $quotation->user,
        ]);

        // Configurar el PDF
        $pdf->setPaper('a4');

        // Guardar el PDF temporalmente
        $filename = 'cotizacion_' . $quotation->quotation_number . '.pdf';
        $tempPath = 'temp/' . $filename;
        Storage::put($tempPath, $pdf->output());

        // Obtener datos para el correo
        $email = $request->input('email');
        $subject = $request->input('subject') ?? 'Cotización ' . $quotation->quotation_number;
        $message = $request->input('message') ?? 'Adjuntamos la cotización solicitada.';

        // Enviar el correo con el PDF adjunto
        \Mail::send('emails.quotation', [
            'quotation' => $quotation,
            'message' => $message,
        ], function ($mail) use ($email, $subject, $tempPath, $filename) {
            $mail->to($email)
                ->subject($subject)
                ->attach(Storage::path($tempPath), [
                    'as' => $filename,
                    'mime' => 'application/pdf',
                ]);
        });

        // Eliminar el archivo temporal
        Storage::delete($tempPath);

        // Devolver respuesta
        return response()->json([
            'success' => true,
            'message' => 'Cotización enviada correctamente a ' . $email,
        ]);
    }
}
