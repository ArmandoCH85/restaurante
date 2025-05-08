<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Livewire\TableMap\TableMapView;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComprobanteController;

Route::get('/', function () {
    // Redirección automática a /admin
    return redirect('/admin');
});

// Ruta para redirigir a los usuarios según su rol
Route::get('/delivery-redirect', [\App\Http\Controllers\DeliveryRedirectController::class, 'redirectBasedOnRole'])
    ->name('delivery.redirect')
    ->middleware(['auth']);

// Rutas del sistema POS
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
    Route::get('/invoices/print/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('invoices.print');
    Route::get('/pos/invoice/pdf/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('pos.invoice.pdf');
    Route::post('/invoices/void/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'voidInvoice'])->name('invoices.void');
});

// Rutas para el proceso unificado de pago y facturación
Route::middleware(['auth'])->group(function () {
    Route::get('/pos/unified/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'showUnifiedForm'])->name('pos.unified.form');
    Route::post('/pos/unified/process/{order}', [\App\Http\Controllers\UnifiedPaymentController::class, 'processUnified'])->name('pos.unified.process');
});

// Rutas para anulación de comprobantes (legacy)
Route::get('/pos/invoices', [PosController::class, 'invoicesList'])->name('pos.invoices.list');
Route::get('/pos/invoice/void/{invoice}', [PosController::class, 'showVoidForm'])->name('pos.void.form');
Route::post('/pos/invoice/void/{invoice}', [PosController::class, 'processVoid'])->name('pos.void.process');
Route::get('/pos/invoice/void-success/{invoice}', [PosController::class, 'voidSuccess'])->name('pos.void.success');

// Rutas para gestión de clientes
Route::get('/pos/customers/find', [PosController::class, 'findCustomer'])->name('pos.customers.find');
Route::post('/pos/customers/store', [PosController::class, 'storeCustomer'])->name('pos.customers.store');

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

// Ruta del mapa de mesas y delivery
Route::get('/tables', TableMapView::class)->name('tables.map');

// Rutas para pedidos de delivery
Route::get('/delivery/order/{orderId}', [\App\Http\Controllers\DeliveryOrderDetailsController::class, 'show'])->name('delivery.order.details');
Route::put('/delivery/order/{deliveryOrderId}/update-status', [\App\Http\Controllers\DeliveryOrderDetailsController::class, 'updateStatus'])->name('delivery.update-status');

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

// Rutas para cierre de caja
Route::get('/admin/cash-register/{cashRegister}/print', [CashRegisterController::class, 'print'])
    ->name('admin.cash-register.print')
    ->middleware(['auth']);

// Rutas para mantenimiento de mesas
Route::middleware(['auth'])->group(function () {
    Route::get('/tables/maintenance', [TableController::class, 'index'])->name('tables.maintenance');
    Route::get('/tables/create', [TableController::class, 'create'])->name('tables.create');
    Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
    Route::get('/tables/{table}/edit', [TableController::class, 'edit'])->name('tables.edit');
    Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
    Route::patch('/tables/{table}/update-status', [TableController::class, 'updateStatus'])->name('tables.update-status');
    Route::delete('/tables/{table}', [TableController::class, 'destroy'])->name('tables.destroy');
    Route::post('/admin/facturacion/comprobantes', [ComprobanteController::class, 'store'])->name('comprobantes.store');
    // Ruta para el dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});


