<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DocumentSeries;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Employee;
use App\Models\CashRegister;
use App\Services\SunatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Controllers\Controller;
use CodersFree\LaravelGreenter\Facades\Greenter;
use Illuminate\Support\Facades\Storage;

class SunatTestController extends Controller
{
    protected $sunatService;

    public function __construct(SunatService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    /**
     * Mostrar vista de prueba con datos aleatorios
     */
    public function index()
    {
        Log::info('🧪 [SUNAT TEST] Iniciando vista de prueba de facturación');

        try {
            // Generar datos aleatorios para la prueba
            $testData = $this->generateRandomTestData();
            
            Log::info('🧪 [SUNAT TEST] Datos de prueba generados exitosamente', [
                'customer_type' => $testData['customer']['document_type'],
                'customer_document' => $testData['customer']['document_number'],
                'products_count' => count($testData['products']),
                'total_amount' => $testData['totals']['total']
            ]);

            return view('sunat-test.index', compact('testData'));

        } catch (Exception $e) {
            Log::error('🚨 [SUNAT TEST] Error generando datos de prueba', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('sunat-test.index', [
                'testData' => null,
                'error' => 'Error generando datos de prueba: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar envío de factura de prueba a SUNAT
     */
    public function sendToSunat(Request $request)
    {
        $testId = uniqid('TEST_');
        Log::info("🧪 [SUNAT TEST - $testId] Iniciando proceso de envío a SUNAT");

        try {
            // Crear orden y factura de prueba
            $invoice = $this->createTestInvoice($request->all(), $testId);
            
            Log::info("🧪 [SUNAT TEST - $testId] Factura de prueba creada", [
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'invoice_type' => $invoice->invoice_type,
                'customer_document' => $invoice->customer->document_number,
                'total' => $invoice->total
            ]);

            // Enviar a SUNAT usando el servicio existente
            Log::info("🧪 [SUNAT TEST - $testId] Iniciando envío a SUNAT vía SunatService");
            
            $result = $this->sunatService->emitirFactura($invoice->id);

            // Recargar la factura para obtener el estado actualizado
            $invoice->refresh();

            Log::info("🧪 [SUNAT TEST - $testId] Proceso completado", [
                'success' => $result,
                'sunat_status' => $invoice->sunat_status,
                'sunat_code' => $invoice->sunat_code,
                'sunat_description' => $invoice->sunat_description,
                'xml_generated' => !empty($invoice->xml_path),
                'pdf_generated' => !empty($invoice->pdf_path),
                'cdr_received' => !empty($invoice->cdr_path)
            ]);

            return response()->json([
                'success' => $result,
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'sunat_status' => $invoice->sunat_status,
                'sunat_code' => $invoice->sunat_code,
                'sunat_description' => $invoice->sunat_description,
                'test_id' => $testId,
                'files' => [
                    'xml_path' => $invoice->xml_path,
                    'pdf_path' => $invoice->pdf_path,
                    'cdr_path' => $invoice->cdr_path
                ],
                'message' => $result ? 
                    'Factura enviada exitosamente a SUNAT' : 
                    'Error en el envío a SUNAT'
            ]);

        } catch (Exception $e) {
            Log::error("🚨 [SUNAT TEST - $testId] Error crítico en el proceso", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'test_id' => $testId,
                'message' => 'Error en el proceso de envío: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar datos aleatorios para la prueba
     */
    private function generateRandomTestData()
    {
        Log::info('🧪 [SUNAT TEST] Generando datos aleatorios de prueba');

        // Determinar tipo de comprobante aleatoriamente
        $invoiceType = rand(0, 1) ? 'invoice' : 'receipt';
        
        // Generar cliente según tipo de comprobante
        if ($invoiceType === 'invoice') {
            // Para facturas: RUC de empresa
            $customer = [
                'name' => 'EMPRESA DE PRUEBA S.A.C.',
                'document_type' => 'RUC',
                'document_number' => '20' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'address' => 'AV. PRUEBA ' . rand(100, 999) . ', LIMA',
                'email' => 'prueba@empresa.com'
            ];
        } else {
            // Para boletas: DNI de persona natural
            $names = ['JUAN CARLOS', 'MARIA ELENA', 'CARLOS ANTONIO', 'ANA LUCIA', 'PEDRO JOSE'];
            $lastNames = ['GARCIA LOPEZ', 'RODRIGUEZ SILVA', 'MARTINEZ TORRES', 'FERNANDEZ RUIZ'];
            
            $customer = [
                'name' => $names[array_rand($names)] . ' ' . $lastNames[array_rand($lastNames)],
                'document_type' => 'DNI',
                'document_number' => str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'address' => 'CALLE PRUEBA ' . rand(100, 999),
                'email' => 'cliente@email.com'
            ];
        }

        // Obtener productos aleatorios del menú
        $products = Product::where('active', true)
            ->where('available', true)
            ->where('product_type', '!=', 'ingredient')
            ->inRandomOrder()
            ->limit(rand(2, 5))
            ->get();

        if ($products->isEmpty()) {
            throw new Exception('No hay productos disponibles para la prueba');
        }

        $productDetails = [];
        $subtotal = 0;

        foreach ($products as $product) {
            $quantity = rand(1, 3);
            $unitPrice = $product->sale_price;
            $lineTotal = $quantity * $unitPrice;
            
            $productDetails[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $lineTotal
            ];
            
            $subtotal += $lineTotal;
        }

        // Calcular IGV (18%) y total
        $igv = round($subtotal * 0.18, 2);
        $total = $subtotal + $igv;

        $testData = [
            'invoice_type' => $invoiceType,
            'customer' => $customer,
            'products' => $productDetails,
            'totals' => [
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ]
        ];

        Log::info('🧪 [SUNAT TEST] Datos de prueba generados', [
            'invoice_type' => $invoiceType,
            'customer_type' => $customer['document_type'],
            'products_count' => count($productDetails),
            'total_amount' => $total
        ]);

        return $testData;
    }

    /**
     * Crear factura de prueba en la base de datos
     */
    private function createTestInvoice($data, $testId)
    {
        Log::info("🧪 [SUNAT TEST - $testId] Creando factura de prueba en BD");

        return DB::transaction(function () use ($data, $testId) {
            // Crear o encontrar cliente
            $customer = Customer::firstOrCreate([
                'document_number' => $data['customer']['document_number']
            ], [
                'name' => $data['customer']['name'],
                'document_type' => $data['customer']['document_type'],
                'address' => $data['customer']['address'],
                'email' => $data['customer']['email']
            ]);

            Log::info("🧪 [SUNAT TEST - $testId] Cliente procesado", [
                'customer_id' => $customer->id,
                'document_type' => $customer->document_type,
                'document_number' => $customer->document_number
            ]);

            // Obtener caja registradora activa o crear una de prueba
            $cashRegister = CashRegister::where('is_active', true)->first();
            if (!$cashRegister) {
                Log::warning("🧪 [SUNAT TEST - $testId] No hay caja activa, creando caja de prueba");
                $employee = Employee::first();
                if (!$employee) {
                    throw new Exception('No hay empleados registrados para asignar la caja');
                }
                
                $cashRegister = CashRegister::create([
                    'employee_id' => $employee->id,
                    'opening_amount' => 100.00,
                    'is_active' => true,
                    'opened_at' => now()
                ]);
            }

            // Crear orden de prueba
            $order = Order::create([
                'customer_id' => $customer->id,
                'employee_id' => $cashRegister->employee_id,
                'cash_register_id' => $cashRegister->id,
                'service_type' => 'takeout',
                'status' => 'completed',
                'subtotal' => $data['totals']['subtotal'],
                'tax' => $data['totals']['igv'],
                'total' => $data['totals']['total'],
                'billed' => false
            ]);

            // Crear detalles de la orden
            foreach ($data['products'] as $productData) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'subtotal' => $productData['total']
                ]);
            }

            Log::info("🧪 [SUNAT TEST - $testId] Orden creada", [
                'order_id' => $order->id,
                'details_count' => count($data['products'])
            ]);

            // Obtener serie correspondiente
            $seriesPrefix = $data['invoice_type'] === 'invoice' ? 'F' : 'B';
            $series = DocumentSeries::where('document_type', $data['invoice_type'])
                ->where('series', 'like', $seriesPrefix . '%')
                ->where('active', true)
                ->first();

            if (!$series) {
                // Crear serie de prueba si no existe
                $series = DocumentSeries::create([
                    'document_type' => $data['invoice_type'],
                    'series' => $seriesPrefix . '001',
                    'current_number' => 0,
                    'active' => true
                ]);
                Log::info("🧪 [SUNAT TEST - $testId] Serie de prueba creada", ['series' => $series->series]);
            }

            // Obtener siguiente número correlativo
            $nextNumber = $series->getNextNumber();

            Log::info("🧪 [SUNAT TEST - $testId] Numeración obtenida", [
                'series' => $series->series,
                'number' => $nextNumber
            ]);

            // Crear factura
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'invoice_type' => $data['invoice_type'],
                'series' => $series->series,
                'number' => $nextNumber,
                'issue_date' => now(),
                'taxable_amount' => $data['totals']['subtotal'],
                'tax' => $data['totals']['igv'],
                'total' => $data['totals']['total'],
                'payment_method' => 'cash',
                'sunat_status' => 'PENDIENTE'
            ]);

            // Crear detalles de la factura
            foreach ($data['products'] as $productData) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'subtotal' => $productData['total']
                ]);
            }

            // Marcar orden como facturada
            $order->update(['billed' => true]);

            Log::info("🧪 [SUNAT TEST - $testId] Factura creada exitosamente", [
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'invoice_type' => $invoice->invoice_type,
                'total' => $invoice->total
            ]);

            return $invoice;
        });
    }

    /**
     * Ver logs del sistema en tiempo real
     */
    public function logs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Archivo de log no encontrado']);
        }

        // Obtener las últimas 50 líneas del log
        $logs = array_slice(file($logFile), -50);
        
        // Filtrar solo logs relacionados con SUNAT TEST
        $sunatLogs = array_filter($logs, function($line) {
            return strpos($line, 'SUNAT TEST') !== false || strpos($line, 'SUNAT') !== false;
        });

        return response()->json([
            'logs' => array_values($sunatLogs),
            'total_lines' => count($sunatLogs),
            'last_update' => date('Y-m-d H:i:s')
        ]);
    }
}