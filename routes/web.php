<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Livewire\TableMap\TableMapView;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashRegisterReportController;
use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Models\CompanyConfig;
use Illuminate\Support\Facades\Blade;
use App\Http\Controllers\PreBillPrintController;

Route::get('/', function () {
    // Redirección automática a /admin
    return redirect('/admin');
});

// Ruta para redirigir a los usuarios según su rol
Route::get('/delivery-redirect', [\App\Http\Controllers\DeliveryRedirectController::class, 'redirectBasedOnRole'])
    ->name('delivery.redirect')
    ->middleware(['auth']);

// Ruta para exportar detalle de caja a PDF
Route::get('/cash-register-reports/{cashRegister}/export-pdf', [CashRegisterReportController::class, 'exportDetailPdf'])
    ->name('cash.register.reports.export.pdf')
    ->middleware(['auth']); // Asegurar que solo usuarios autenticados puedan acceder

// Ruta para exportar informe de caja a PDF (sin cuadrículas)
Route::get('/admin/export-cash-register-pdf/{id}', [\App\Http\Controllers\Filament\ExportCashRegisterPdfController::class, 'export'])
    ->middleware(['web', 'auth'])
    ->name('filament.admin.export-cash-register-pdf');

// Rutas del sistema POS
// Usar el middleware personalizado para verificar el permiso 'access_pos'
Route::middleware(['auth', 'pos.access'])->group(function () {
    Route::get('/pos', [App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/table/{table}', [PosController::class, 'showTable'])->name('pos.table');

    // Rutas para PDFs - Accesibles para todos los roles autenticados
    Route::get('/pos/command-pdf/{order}', [PosController::class, 'generateCommandPdf'])->name('pos.command.pdf');
    Route::get('/pos/prebill-pdf/{order}', [PosController::class, 'generatePreBillPdf'])->name('pos.prebill.pdf');
    Route::get('/pos/command/generate', [PosController::class, 'createAndShowCommand'])->name('pos.command.generate');
    Route::get('/pos/prebill/generate', [PosController::class, 'createAndShowPreBill'])->name('pos.prebill.generate');

    Route::get('/pos/invoice/generate', [PosController::class, 'createAndShowInvoiceForm'])->name('pos.invoice.create');

    // Ruta para crear orden desde JavaScript
    Route::post('/pos/create-order', [PosController::class, 'createOrderFromJS'])->name('pos.create-order');
});

// Rutas para pagos - Solo cashiers, admin y super_admin
Route::middleware(['auth', 'role:cashier|admin|super_admin'])->group(function () {
    Route::get('/pos/payment/form/{order}', [\App\Http\Controllers\PaymentController::class, 'showPaymentForm'])->name('pos.payment.form');
    Route::post('/pos/payment/process/{order}', [\App\Http\Controllers\PaymentController::class, 'processPayment'])->name('pos.payment.process');
    Route::get('/pos/payment/history/{order}', [\App\Http\Controllers\PaymentController::class, 'showPaymentHistory'])->name('pos.payment.history');
    Route::post('/pos/payment/void/{payment}', [\App\Http\Controllers\PaymentController::class, 'voidPayment'])->name('pos.payment.void');
});

// Rutas para facturación - Solo cashiers, admin y super_admin
Route::middleware(['auth', 'role:cashier|admin|super_admin'])->group(function () {
    Route::get('/pos/invoice/form/{order}', [\App\Http\Controllers\InvoiceController::class, 'showInvoiceForm'])->name('pos.invoice.form');
    Route::post('/pos/invoice/generate/{order}', [\App\Http\Controllers\InvoiceController::class, 'generateInvoice'])->name('pos.invoice.generate');
    Route::get('/pos/invoice/pdf/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('pos.invoice.pdf');
    Route::post('/invoices/void/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'voidInvoice'])->name('invoices.void');

    // Rutas para vista previa térmica (solo para desarrollo/pruebas)
    Route::get('/thermal-preview/invoice/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'thermalPreview'])->name('thermal.preview.invoice');
});

// Ruta para imprimir ticket (abre PDF directamente) evitando respuesta JSON Livewire
Route::middleware(['auth','role:cashier|admin|super_admin'])->get('/admin/invoices/{invoice}/print-ticket', [\App\Http\Controllers\InvoiceController::class, 'printTicket'])
    ->name('filament.admin.invoices.print-ticket');

// Ruta de impresión de comprobantes - DESHABILITADA - ahora se usa la ruta de Filament
// Route::get('/invoices/print/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('invoices.print');
Route::get('/thermal-preview/command/{order}', [\App\Http\Controllers\PosController::class, 'thermalPreviewCommand'])->name('thermal.preview.command');
Route::get('/thermal-preview/pre-bill/{order}', [\App\Http\Controllers\PosController::class, 'thermalPreviewPreBill'])->name('thermal.preview.prebill');
Route::get('/thermal-preview/demo', function() {
    return view('thermal-preview');
})->name('thermal.preview.demo');

// Rutas para el proceso unificado de pago y facturación - Solo cashiers, admin y super_admin
Route::middleware(['auth', 'role:cashier|admin|super_admin'])->group(function () {
    Route::get('/pos/unified/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'showUnifiedForm'])->name('pos.unified.form');
    Route::post('/pos/unified/process/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'processUnified'])->name('pos.unified.process');
});

// Rutas para anulación de comprobantes (legacy) y gestión de clientes
Route::middleware(['auth', 'pos.access'])->group(function () {
    // Rutas para anulación de comprobantes - Solo cashiers, admin y super_admin
    Route::middleware(['role:cashier|admin|super_admin'])->group(function () {
        Route::get('/pos/invoices', [PosController::class, 'invoicesList'])->name('pos.invoices.list');
        Route::get('/pos/invoice/void/{invoice}', [PosController::class, 'showVoidForm'])->name('pos.void.form');
        Route::post('/pos/invoice/void/{invoice}', [PosController::class, 'processVoid'])->name('pos.void.process');
        Route::get('/pos/invoice/void-success/{invoice}', [PosController::class, 'voidSuccess'])->name('pos.void.success');
    });

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

// Ruta para aprobar caja registradora
Route::post('/admin/operaciones-caja/approve/{id}', [\App\Http\Controllers\CashRegisterApprovalController::class, 'approve'])
    ->middleware(['web', 'auth'])
    ->name('filament.admin.cash-register.approve');

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

// Ruta optimizada para pre-cuentas
Route::middleware(['web'])->group(function () {
    Route::get('/print/prebill/{order}', [PreBillPrintController::class, 'show'])
        ->name('print.prebill')
        ->middleware(['auth']);
});

// Ruta optimizada para impresión térmica desde POS (ÚNICA VERSIÓN)
Route::get('/print/invoice/{invoice}', function(Invoice $invoice) {
    // Log para debugging
    Log::info('🖨️ ACCESO A RUTA DE IMPRESIÓN', [
        'invoice_id' => $invoice->id,
        'invoice_type' => $invoice->invoice_type,
        'user_id' => auth()->check() ? auth()->user()->id : null,
        'timestamp' => now()
    ]);

    // Obtener configuración de empresa usando los métodos estáticos
    $company = [
        'ruc' => CompanyConfig::getRuc(),
        'razon_social' => CompanyConfig::getRazonSocial(),
        'nombre_comercial' => CompanyConfig::getNombreComercial(),
        'direccion' => CompanyConfig::getDireccion(),
        'telefono' => CompanyConfig::getTelefono(),
        'email' => CompanyConfig::getEmail(),
    ];

    // Datos para el PDF/HTML
    $invoice->load(['customer', 'details.product', 'order.table']);
    // Pasar contacto solo para Nota de Venta en venta directa (sin mesa, no delivery)
    $directSaleName = null;
    if ($invoice->invoice_type === 'sales_note' && ($invoice->order && empty($invoice->order->table_id)) && ($invoice->order->service_type ?? null) !== 'delivery') {
        $directSaleName = session('direct_sale_customer_name');
    }

    $data = [
        'invoice' => $invoice,
        'company' => $company,
        'direct_sale_customer_name' => $directSaleName
    ];

    // Determinar la vista según el tipo de documento
    $view = match($invoice->invoice_type) {
        'receipt' => 'pdf.receipt',
        'sales_note' => 'pdf.sales_note',
        default => 'pdf.invoice'
    };

    try {
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

        Log::info('✅ HTML DE IMPRESIÓN GENERADO CORRECTAMENTE', ['invoice_id' => $invoice->id]);

        // Limpiar nombre de venta directa para no reutilizarlo
        if ($invoice->invoice_type === 'sales_note') {
            session()->forget('direct_sale_customer_name');
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

    } catch (\Exception $e) {
        Log::error('❌ ERROR EN RUTA DE IMPRESIÓN', [
            'invoice_id' => $invoice->id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response('Error al generar comprobante: ' . $e->getMessage(), 500);
    }
})->middleware(['web'])->name('print.invoice');

// Ruta de prueba para verificar URL de imagen
Route::get('/test-image-url', function() {
    $product = \App\Models\Product::where('image_path', 'productos/01JXC0MBGKBM6V4WY9TMBQEDPT.png')->first();
    if ($product) {
        return [
            'image_path' => $product->image_path,
            'full_url' => asset('storage/' . $product->image_path),
            'storage_path' => storage_path('app/public/' . $product->image_path),
            'exists' => file_exists(storage_path('app/public/' . $product->image_path))
        ];
    }
    return 'Producto no encontrado';
});

// Rutas para visualización individual de reportes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/reportes/{category}/{reportType}', function($category, $reportType) {
        try {
            // Crear una instancia de la página y configurar propiedades públicas
            $page = new \App\Filament\Pages\ReportViewerPage;

            // Configurar propiedades directamente
            $page->category = $category;
            $page->reportType = $reportType;

            // Manejar parámetros de filtro desde la URL
            $dateRange = request('dateRange', 'today');
            $page->dateRange = $dateRange;

            // Si es filtro personalizado, tomar fechas y horas de la URL
            if ($dateRange === 'custom') {
                $page->startDate = request('startDate', now()->format('Y-m-d'));
                $page->endDate = request('endDate', now()->format('Y-m-d'));
                $page->startTime = request('startTime');
                $page->endTime = request('endTime');
                $page->invoiceType = request('invoiceType');
            } else {
                // Configurar fechas según el filtro predeterminado
                $page->setDateRange($dateRange);
            }

            // Cargar datos del reporte
            $page->loadReportData();

            // Renderizar usando la vista Blade directamente
            return view('filament.pages.report-viewer-page', [
                'page' => $page,
                'reportType' => $reportType,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            // En caso de error, mostrar mensaje amigable
            return response()->view('errors.generic', [
                'message' => 'Error al cargar el reporte: ' . $e->getMessage(),
                'backUrl' => route('filament.admin.pages.reportes')
            ], 500);
        }
    })->name('filament.admin.pages.report-viewer');

    // Rutas para modal de detalle y impresión de órdenes
    Route::get('/admin/orders/{order}/detail', function($orderId) {
        try {
            $order = \App\Models\Order::with(['customer', 'user', 'table', 'orderDetails.product'])->findOrFail($orderId);

            return view('filament.modals.order-detail', compact('order'));
        } catch (\Exception $e) {
            return response('<div class="text-center p-4 text-red-600">Error: ' . $e->getMessage() . '</div>', 404);
        }
    })->name('admin.orders.detail');

    Route::get('/admin/orders/{order}/print', function($orderId) {
        try {
            $order = \App\Models\Order::with(['customer', 'user', 'table', 'orderDetails.product'])->findOrFail($orderId);

            return view('filament.modals.order-print', compact('order'));
        } catch (\Exception $e) {
            return response('Error al cargar la orden: ' . $e->getMessage(), 500);
        }
    })->name('admin.orders.print');
});

// 🧪 RUTAS DE PRUEBA SUNAT
Route::middleware(['auth'])->group(function () {
    // Vista principal de prueba SUNAT
    Route::get('/sunat-test', [\App\Http\Controllers\SunatTestController::class, 'index'])
        ->name('sunat-test.index');
    
    // Envío de factura de prueba a SUNAT
    Route::post('/sunat-test/send', [\App\Http\Controllers\SunatTestController::class, 'sendToSunat'])
        ->name('sunat-test.send');
    
    // Obtener logs del sistema en tiempo real
    Route::get('/sunat-test/logs', [\App\Http\Controllers\SunatTestController::class, 'logs'])
        ->name('sunat-test.logs');
});



