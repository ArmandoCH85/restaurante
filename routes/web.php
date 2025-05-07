<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Livewire\TableMap\TableMapView;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas del sistema POS
// Check if there's a route like this:
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

// Rutas para facturación
Route::get('/pos/invoice/form/{order}', [PosController::class, 'invoiceForm'])->name('pos.invoice.form');
Route::post('/pos/invoice/generate/{order}', [PosController::class, 'generateInvoice'])->name('pos.invoice.generate');
Route::get('/pos/invoice/pdf/{invoice}', [PosController::class, 'downloadInvoicePdf'])->name('pos.invoice.pdf');

// Rutas para anulación de comprobantes
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

// Ruta del mapa de mesas
Route::get('/tables', TableMapView::class)->name('tables.map');

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

    // Ruta para el dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
