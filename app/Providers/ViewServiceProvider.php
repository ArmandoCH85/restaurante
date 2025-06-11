<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\InvoiceComposer;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar compositores de vistas
        View::composer([
            'pdf.invoice',
            'pdf.receipt',
            'pdf.sales_note',
            'pdf.comanda',
            'pdf.prebill'
        ], InvoiceComposer::class);
    }
}
