<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Livewire\TableMap\TableMapView;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\DashboardController;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\CompanyConfig;
use Illuminate\Support\Facades\Blade;

// Incluir rutas específicas para impresión
require __DIR__ . '/web-print.php';

Route::get('/', function () {
    // Redirección automática a /admin
    return redirect('/admin');
});

// Ruta para redirigir a los usuarios según su rol
Route::get('/delivery-redirect', [\App\Http\Controllers\DeliveryRedirectController::class, 'redirectBasedOnRole'])
    ->name('delivery.redirect')
    ->middleware(['auth']);

// Rutas del sistema POS
// Usar el middleware personalizado para verificar el permiso 'access_pos'
Route::middleware(['auth', 'pos.access'])->group(function () {
    Route::get('/pos', [App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/table/{table}', [PosController::class, 'showTable'])->name('pos.table');

    // Rutas para PDFs
    Route::get('/pos/command-pdf/{order}', [PosController::class, 'generateCommandPdf'])->name('pos.command.pdf');
    Route::get('/pos/prebill-pdf/{order}', [PosController::class, 'generatePreBillPdf'])->name('pos.prebill.pdf');

    // Nuevas rutas directas para generar y mostrar documentos
    Route::get('/pos/command/generate', [PosController::class, 'createAndShowCommand'])->name('pos.command.generate');
    Route::get('/pos/prebill/generate', [PosController::class, 'createAndShowPreBill'])->name('pos.prebill.generate');
    Route::get('/pos/invoice/generate', [PosController::class, 'createAndShowInvoiceForm'])->name('pos.invoice.create');

    // Ruta para crear orden desde JavaScript
    Route::post('/pos/create-order', [PosController::class, 'createOrderFromJS'])->name('pos.create-order');
});

// Rutas para pagos
Route::middleware(['auth'])->group(function () {
    Route::get('/pos/payment/form/{order}', [\App\Http\Controllers\PaymentController::class, 'showPaymentForm'])->name('pos.payment.form');
    Route::post('/pos/payment/process/{order}', [\App\Http\Controllers\PaymentController::class, 'processPayment'])->name('pos.payment.process');
    Route::get('/pos/payment/history/{order}', [\App\Http\Controllers\PaymentController::class, 'showPaymentHistory'])->name('pos.payment.history');
    Route::post('/pos/payment/void/{payment}', [\App\Http\Controllers\PaymentController::class, 'voidPayment'])->name('pos.payment.void');
});

// Rutas para facturación
Route::middleware(['auth'])->group(function () {
    Route::get('/pos/invoice/form/{order}', [\App\Http\Controllers\InvoiceController::class, 'showInvoiceForm'])->name('pos.invoice.form');
    Route::post('/pos/invoice/generate/{order}', [\App\Http\Controllers\InvoiceController::class, 'generateInvoice'])->name('pos.invoice.generate');
    Route::get('/pos/invoice/pdf/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('pos.invoice.pdf');
    Route::post('/invoices/void/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'voidInvoice'])->name('invoices.void');

    // Rutas para vista previa térmica (solo para desarrollo/pruebas)
    Route::get('/thermal-preview/invoice/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'thermalPreview'])->name('thermal.preview.invoice');
});

// Ruta de impresión de comprobantes - DESHABILITADA - ahora se usa la ruta de Filament
// Route::get('/invoices/print/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('invoices.print');
Route::get('/thermal-preview/command/{order}', [\App\Http\Controllers\PosController::class, 'thermalPreviewCommand'])->name('thermal.preview.command');
Route::get('/thermal-preview/pre-bill/{order}', [\App\Http\Controllers\PosController::class, 'thermalPreviewPreBill'])->name('thermal.preview.prebill');
Route::get('/thermal-preview/demo', function() {
    return view('thermal-preview');
})->name('thermal.preview.demo');

// Rutas para el proceso unificado de pago y facturación
Route::middleware(['auth'])->group(function () {
    Route::get('/pos/unified/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'showUnifiedForm'])->name('pos.unified.form');
    Route::post('/pos/unified/process/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'processUnified'])->name('pos.unified.process');
});

// Rutas para anulación de comprobantes (legacy) y gestión de clientes
Route::middleware(['auth', 'pos.access'])->group(function () {
    // Rutas para anulación de comprobantes
    Route::get('/pos/invoices', [PosController::class, 'invoicesList'])->name('pos.invoices.list');
    Route::get('/pos/invoice/void/{invoice}', [PosController::class, 'showVoidForm'])->name('pos.void.form');
    Route::post('/pos/invoice/void/{invoice}', [PosController::class, 'processVoid'])->name('pos.void.process');
    Route::get('/pos/invoice/void-success/{invoice}', [PosController::class, 'voidSuccess'])->name('pos.void.success');

    // Rutas para gestión de clientes
    Route::get('/pos/customers/find', [PosController::class, 'findCustomer'])->name('pos.customers.find');
    Route::get('/pos/customers/search', [PosController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('/pos/customers/store', [PosController::class, 'storeCustomer'])->name('pos.customers.store');
});

// Ruta de prueba para imágenes
Route::get('/test-images', function() {
    $products = \App\Models\Product::whereNotNull('image_path')->limit(5)->get();
    return view('test-images', ['products' => $products]);
});

// Nueva ruta de prueba para imágenes
Route::get('/image-test', function() {
    $products = \App\Models\Product::whereNotNull('image_path')->limit(10)->get();
    return view('image-test', ['products' => $products]);
});

// Rutas del mapa de mesas y delivery
Route::get('/tables', TableMapView::class)
    ->name('tables.map')
    ->middleware(['auth', 'tables.access']);

// La ruta para el mapa de mesas ahora se maneja a través del panel Filament en /admin/mapa-mesas

// Rutas para pedidos de delivery
Route::middleware(['auth', 'delivery.access'])->group(function () {
    Route::get('/delivery/order/{orderId}', [\App\Http\Controllers\DeliveryOrderDetailsController::class, 'show'])->name('delivery.order.details');
    Route::put('/delivery/order/{deliveryOrderId}/update-status', [\App\Http\Controllers\DeliveryOrderDetailsController::class, 'updateStatus'])->name('delivery.update-status');
});

// Nuevas rutas para gestión de delivery
Route::middleware(['auth'])->group(function () {
    // Delivery - Administradores
    Route::get('/delivery/manage', \App\Livewire\Delivery\DeliveryManager::class)
        ->name('delivery.manage')
        ->middleware('role:super_admin|admin');

    // Delivery - Repartidores
    Route::get('/delivery/my-orders', \App\Livewire\Delivery\DeliveryDriver::class)
        ->name('delivery.my-orders')
        ->middleware('role:delivery');
});

// Ruta para resetear el estado de todas las mesas a disponible
Route::get('/tables/reset-status', [\App\Http\Controllers\TableResetController::class, 'resetAllTables'])->name('tables.reset-status');



// Rutas para mantenimiento de mesas
Route::middleware(['auth', 'tables.maintenance.access'])->group(function () {
    Route::get('/tables/maintenance', [TableController::class, 'index'])->name('tables.maintenance');
    Route::get('/tables/create', [TableController::class, 'create'])->name('tables.create');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::get('/tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
    Route::patch('/tables/{table}/update-status', [TableController::class, 'updateStatus'])->name('tables.update-status');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
    // Ruta para comprobantes (comentada hasta resolver el controlador)
    // Route::post('/admin/facturacion/comprobantes', [ComprobanteController::class, 'store'])->name('comprobantes.store');
    // Ruta para el dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});

// Ruta para impresión de cierre de caja (dentro del panel de Filament)
Route::get('/admin/print-cash-register/{id}', \App\Http\Controllers\Filament\PrintCashRegisterController::class)
    ->middleware(['web', 'auth'])
    ->name('filament.admin.print-cash-register');

// Rutas para cotizaciones
Route::middleware(['web', 'auth'])->group(function () {
    // Ver PDF de cotización
    Route::get('/admin/quotations/{quotation}/print', [\App\Http\Controllers\Filament\QuotationPdfController::class, 'show'])
        ->name('filament.admin.resources.quotations.print');

    // Descargar PDF de cotización
    Route::get('/admin/quotations/{quotation}/download', [\App\Http\Controllers\Filament\QuotationPdfController::class, 'download'])
        ->name('filament.admin.resources.quotations.download');

    // Enviar cotización por correo electrónico
    Route::post('/admin/quotations/{quotation}/email', [\App\Http\Controllers\Filament\QuotationPdfController::class, 'email'])
        ->name('filament.admin.resources.quotations.email');

    // RUTAS DE PDF DESHABILITADAS - SE USAN LAS PÁGINAS PERSONALIZADAS DE FILAMENT
    // Las rutas están manejadas por:
    // - app/Filament/Resources/InvoiceResource/Pages/PrintInvoice.php
    // - app/Filament/Resources/InvoiceResource/Pages/DownloadInvoice.php
    // URLs generadas automáticamente por Filament:
    // - /admin/facturacion/comprobantes/{record}/print
    // - /admin/facturacion/comprobantes/{record}/download
});

// Rutas para descargas de comprobantes SUNAT
Route::middleware(['web', 'auth'])->group(function () {
    // Descargar XML del comprobante
    Route::get('/admin/invoices/{invoice}/download-xml', [\App\Http\Controllers\InvoiceController::class, 'downloadXml'])
        ->name('filament.admin.invoices.download-xml');

    // Descargar CDR del comprobante
    Route::get('/admin/invoices/{invoice}/download-cdr', [\App\Http\Controllers\InvoiceController::class, 'downloadCdr'])
        ->name('filament.admin.invoices.download-cdr');

    // Descargar PDF del comprobante
    Route::get('/admin/invoices/{invoice}/download-pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])
        ->name('filament.admin.invoices.download-pdf');
});

Route::get('/invoices/{invoice}/download-pdf', function(Invoice $invoice) {
    // Determinar la vista según el tipo de comprobante
    $view = match($invoice->invoice_type) {
        'receipt' => 'pdf.receipt',
        'sales_note' => 'pdf.sales_note',
        default => 'pdf.invoice'
    };

    $pdf = Pdf::loadView($view, compact('invoice'));
    return $pdf->stream("comprobante-{$invoice->id}.pdf");
})->name('invoices.download-pdf');

Route::get('/orders/{order}/download-comanda-pdf', function(Order $order) {
    // ✅ Capturar el nombre del cliente desde la URL para venta directa
    $customerNameForComanda = request()->get('customerName', '');

    // Siempre asegurarse de tener un cliente, incluso si es genérico
    $customer = $order->customer ?? \App\Models\Customer::getGenericCustomer();

    // Verificar que el cliente no sea nulo
    if (!$customer) {
        $customer = \App\Models\Customer::getGenericCustomer();
    }

    $pdf = Pdf::loadView('pdf.comanda', compact('order', 'customerNameForComanda', 'customer'));
    return $pdf->stream("comanda-{$order->id}.pdf");
})->name('orders.comanda.pdf');

// Ruta eliminada para evitar conflicto - se usa la ruta pos.prebill.pdf existente

// RUTA DE PRUEBA SIMPLE (SIN AUTH) PARA DEBUGGING
Route::get('/test-print/{invoice}', function(\App\Models\Invoice $invoice) {
    Log::info('🧪 RUTA DE PRUEBA ACCEDIDA', ['invoice_id' => $invoice->id]);
    return response('✅ Ruta funcionando - Factura ID: ' . $invoice->id);
})->name('test.print');

// RUTAS SIMPLES PARA IMPRESIÓN DE PDFs (SOLUCIÓN KISS) - SIN AUTH PARA WINDOWS POPUP
Route::middleware(['web'])->group(function () {
    // Ruta simple para imprimir comprobantes desde POS
    Route::get('/print/invoice/{invoice}', function(\App\Models\Invoice $invoice) {
        Log::info('🖨️ ACCESO A RUTA DE IMPRESIÓN', [
            'invoice_id' => $invoice->id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        $customer = $invoice->customer ?? Customer::getGenericCustomer();

        // Obtener información de la empresa desde configuración usando CompanyConfig
        $company = (object) [
            'name' => \App\Models\CompanyConfig::getRazonSocial() ?? 'Mi Empresa',
            'nombre_comercial' => \App\Models\CompanyConfig::getNombreComercial() ?? 'Mi Empresa',
            'ruc' => \App\Models\CompanyConfig::getRuc() ?? '12345678901',
            'address' => \App\Models\CompanyConfig::getDireccion() ?? 'Dirección no configurada',
            'phone' => \App\Models\CompanyConfig::getTelefono() ?? 'Teléfono no configurado',
            'email' => \App\Models\CompanyConfig::getEmail() ?? 'email@empresa.com'
        ];

        // Determinar la vista según el tipo de comprobante
        $view = match($invoice->invoice_type) {
            'invoice' => 'pdf.invoice',
            'receipt' => 'pdf.receipt',
            'sales_note' => 'pdf.sales_note',
            default => 'pdf.sales_note',
        };

        try {
            Log::info('🖨️ GENERANDO PDF', [
                'invoice_id' => $invoice->id,
                'view' => $view,
                'customer' => $customer->name ?? 'Sin cliente'
            ]);

            $pdf = Pdf::loadView($view, compact('invoice', 'customer', 'company'));

            Log::info('✅ PDF GENERADO EXITOSAMENTE', ['invoice_id' => $invoice->id]);

            return $pdf->stream("comprobante-{$invoice->formattedNumber}.pdf");
        } catch (\Exception $e) {
            Log::error('❌ ERROR GENERANDO PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response('Error al generar el PDF: ' . $e->getMessage(), 500);
        }
    })->name('print.invoice');
});

// Nueva ruta KISS para impresión desde POS - usando el mismo patrón que Filament
Route::get('/print/invoice/{invoice}', function(Invoice $invoice) {
    // Obtener configuración de empresa usando los métodos estáticos
    $company = [
        'ruc' => CompanyConfig::getRuc(),
        'razon_social' => CompanyConfig::getRazonSocial(),
        'nombre_comercial' => CompanyConfig::getNombreComercial(),
        'direccion' => CompanyConfig::getDireccion(),
        'telefono' => CompanyConfig::getTelefono(),
        'email' => CompanyConfig::getEmail(),
    ];

    // Datos para el PDF
    $data = [
        'invoice' => $invoice->load(['customer', 'details.product', 'order.table']),
        'company' => $company,
    ];

    // Determinar la vista según el tipo de documento
    $view = match($invoice->invoice_type) {
        'receipt' => 'pdf.receipt',
        'sales_note' => 'pdf.sales_note',
        default => 'pdf.invoice'
    };

        // Generar HTML con JavaScript y CSS optimizado para papel térmico
    $html = Blade::render($view, $data);

    // CSS y JavaScript optimizado para papel térmico 58mm/80mm
    $thermal_optimization = "
    <style>
        /* Estilos optimizados para papel térmico 58mm/80mm */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto; /* Ancho estándar térmico */
            }
            body {
                width: 80mm;
                margin: 0;
                padding: 2mm;
                font-family: 'Courier New', monospace;
                font-size: 10px;
                line-height: 1.1;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .container { width: 100%; max-width: none; }
            .header h1 { font-size: 12px; margin: 0 0 2mm 0; text-align: center; }
            .header p { font-size: 8px; margin: 0; text-align: center; }
            .info-table, .details-table { width: 100%; font-size: 8px; }
            .details-table th, .details-table td { padding: 0.5mm; border: none; }
            .total-section { margin-top: 2mm; font-size: 9px; font-weight: bold; }
            hr { border: none; border-top: 1px dashed #000; margin: 1mm 0; }
            .no-print { display: none !important; }
        }
        @media screen {
            body {
                width: 80mm; margin: 10px auto; padding: 10px;
                border: 1px solid #ccc; background: white;
                font-family: 'Courier New', monospace; font-size: 11px;
            }
        }
    </style>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                console.log('🖨️ Impresión térmica automática iniciada...');
                window.print();
                window.addEventListener('afterprint', function() {
                    setTimeout(() => window.close(), 500);
                });
            }, 800);
        });
    </script>";

    // Insertar optimización térmica antes del cierre de </head> o al inicio de <body>
    if (strpos($html, '</head>') !== false) {
        $html = str_replace('</head>', $thermal_optimization . '</head>', $html);
    } else {
        $html = '<html><head>' . $thermal_optimization . '</head><body>' . $html . '</body></html>';
    }

    return response($html, 200, [
        'Content-Type' => 'text/html; charset=utf-8',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ]);
})->middleware(['web'])->name('print.invoice');


