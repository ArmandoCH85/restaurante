<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use Filament\Resources\Pages\Page;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrintQuotation extends Page
{
    protected static string $resource = QuotationResource::class;

    protected static string $view = 'filament.resources.quotation-resource.pages.print-quotation';

    public Quotation $record;

    public function mount(Quotation $record): void
    {
        $this->record = $record;

        // Generar el PDF y mostrarlo directamente
        $this->generatePdf();
    }

    protected function generatePdf()
    {
        $quotation = $this->record;
        $details = $quotation->details()->with('product')->get();

        // Si no hay customer asociado, usar el cliente genérico
        $customer = $quotation->customer;
        if (!$customer) {
            $customer = \App\Models\Customer::getGenericCustomer();
        }

        $pdf = Pdf::loadView('reports.quotation', [
            'quotation' => $quotation,
            'details' => $details,
            'customer' => $customer,
            'user' => $quotation->user,
        ]);

        // Configurar el PDF
        $pdf->setPaper('a4');

        // Generar un nombre de archivo único
        $filename = 'cotizacion_' . $quotation->quotation_number . '.pdf';

        // Guardar temporalmente el PDF
        $tempPath = 'temp/' . $filename;
        Storage::put($tempPath, $pdf->output());

        // Redirigir al archivo PDF
        return redirect(Storage::url($tempPath));
    }
}
