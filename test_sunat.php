<?php

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\SunatService;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "=== SIMULACIÓN DE ENVÍO A SUNAT ===\n";

// 1. Buscar productos disponibles
echo "\n1. Buscando productos disponibles...\n";
$products = Product::where('active', 1)->where('available', 1)->take(3)->get();
foreach ($products as $product) {
    echo "ID: {$product->id} | {$product->name} | S/ " . number_format($product->sale_price, 2) . "\n";
}

// 2. Buscar o crear cliente
echo "\n2. Buscando cliente de prueba...\n";
$customer = Customer::where('document_number', '99999999')->first();
if (!$customer) {
    $customer = Customer::create([
        'document_type' => 'DNI',
        'document_number' => '99999999',
        'name' => 'Cliente de Prueba',
        'phone' => '999999999',
        'email' => 'test@example.com'
    ]);
    echo "Cliente creado: {$customer->name}\n";
} else {
    echo "Cliente encontrado: {$customer->name}\n";
}

// 3. Buscar empleado
echo "\n3. Buscando empleado...\n";
$employee = Employee::first();
if (!$employee) {
    echo "ERROR: No hay empleados en el sistema\n";
    exit(1);
}
echo "Empleado: {$employee->first_name} {$employee->last_name}\n";

// 4. Crear orden de prueba
echo "\n4. Creando orden de prueba...\n";
$order = new Order([
    'service_type' => 'dine_in',
    'customer_id' => $customer->id,
    'employee_id' => $employee->id,
    'order_datetime' => now(),
    'status' => Order::STATUS_OPEN,
    'billed' => false
]);

$order->save();
echo "Orden creada: ID {$order->id}\n";

// 5. Agregar producto de S/ 2.00
echo "\n5. Agregando producto...\n";
$testProduct = $products->first();
if (!$testProduct) {
    echo "ERROR: No hay productos disponibles\n";
    exit(1);
}

// Modificar precio a S/ 2.00 para la prueba
$order->addProduct($testProduct->id, 1, 2.00);
echo "Producto agregado: {$testProduct->name} x 1 = S/ 2.00\n";

// 6. Calcular totales
$order->recalculateTotals();
echo "Total calculado: S/ " . number_format($order->total, 2) . "\n";

// 7. Generar factura
echo "\n6. Generando factura...\n";
try {
    $invoice = $order->generateInvoice('receipt', 'B002', $customer->id);
    echo "Factura generada: {$invoice->series}-{$invoice->number}\n";
    echo "ID de factura: {$invoice->id}\n";
} catch (Exception $e) {
    echo "ERROR al generar factura: {$e->getMessage()}\n";
    exit(1);
}

// 8. Enviar a SUNAT
echo "\n7. Enviando a SUNAT...\n";
try {
    $sunatService = new SunatService();
    $result = $sunatService->emitirFactura($invoice->id);

    echo "Resultado del envío:\n";
    echo "- Éxito: " . ($result['success'] ? 'SÍ' : 'NO') . "\n";

    if ($result['success']) {
        echo "- Mensaje: {$result['message']}\n";
        echo "- XML Path: {$result['xml_path']}\n";
        echo "- PDF Path: {$result['pdf_path']}\n";
    } else {
        echo "- Error: {$result['message']}\n";
        if (isset($result['error_code'])) {
            echo "- Código de error: {$result['error_code']}\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR al enviar a SUNAT: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}

// 9. Verificar estado final
echo "\n8. Verificando estado final...\n";
$invoice->refresh();
echo "Estado SUNAT: {$invoice->sunat_status}\n";
if ($invoice->sunat_code) {
    echo "Código SUNAT: {$invoice->sunat_code}\n";
}
if ($invoice->sunat_description) {
    echo "Descripción SUNAT: {$invoice->sunat_description}\n";
}

echo "\n=== PRUEBA COMPLETADA ===\n";