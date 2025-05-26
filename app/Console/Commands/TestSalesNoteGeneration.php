<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Payment;

class TestSalesNoteGeneration extends Command
{
    protected $signature = 'test:sales-note';
    protected $description = 'Probar la generación de notas de venta';

    public function handle()
    {
        $this->info("🧾 PROBANDO GENERACIÓN DE NOTA DE VENTA");
        $this->line("");

        try {
            // 1. Crear orden de prueba
            $this->info("1️⃣ Creando orden de prueba...");
            $order = $this->createTestOrder();
            $this->line("   ✅ Orden #{$order->id} creada");

            // 2. Agregar pago para completar la orden
            $this->info("2️⃣ Agregando pago...");
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'payment_method' => 'cash',
                'payment_datetime' => now(),
                'received_by' => $employee->user_id ?? 1 // Usuario del empleado o admin por defecto
            ]);
            $this->line("   ✅ Pago de S/ {$payment->amount} registrado");

            // 3. Generar nota de venta
            $this->info("3️⃣ Generando nota de venta...");
            $invoice = $order->generateInvoice('sales_note', 'NV');

            if ($invoice) {
                $this->line("   ✅ Nota de venta #{$invoice->formatted_number} generada");
                $this->line("   📊 Estado SUNAT: {$invoice->sunat_status}");
                $this->line("   💰 Total: S/ " . number_format($invoice->total, 2));

                $this->line("");
                $this->info("📋 DETALLES DE LA NOTA DE VENTA:");
                $this->line("   🆔 ID: {$invoice->id}");
                $this->line("   📄 Tipo: {$invoice->invoice_type}");
                $this->line("   🔢 Serie-Número: {$invoice->formatted_number}");
                $this->line("   📅 Fecha: {$invoice->issue_date}");
                $this->line("   👤 Cliente: {$invoice->client_name}");
                $this->line("   📊 Estado SUNAT: {$invoice->sunat_status}");
                $this->line("   📊 Estado Tributario: {$invoice->tax_authority_status}");

                $this->line("");
                $this->info("✅ PRUEBA EXITOSA: La nota de venta se generó correctamente");

            } else {
                $this->error("   ❌ No se pudo generar la nota de venta");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error durante la prueba: " . $e->getMessage());
            $this->line("📍 Archivo: " . $e->getFile());
            $this->line("📍 Línea: " . $e->getLine());
            return 1;
        }

        return 0;
    }

    private function createTestOrder(): Order
    {
        // Buscar o crear cliente
        $customer = Customer::firstOrCreate(
            ['document_number' => '12345678'],
            [
                'document_type' => 'dni',
                'name' => 'Cliente Prueba Nota',
                'email' => 'cliente.prueba@email.com',
                'phone' => '987654321',
                'address' => 'Dirección de prueba'
            ]
        );

        // Buscar empleado
        $employee = Employee::first();
        if (!$employee) {
            throw new \Exception('No hay empleados registrados');
        }

        // Crear orden
        $order = Order::create([
            'service_type' => 'dine_in',
            'customer_id' => $customer->id,
            'employee_id' => $employee->id,
            'order_datetime' => now(),
            'status' => Order::STATUS_OPEN,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'notes' => 'Orden de prueba para nota de venta',
            'billed' => false
        ]);

        // Agregar productos
        $products = Product::where('product_type', 'sale_item')
            ->where('active', true)
            ->take(2)
            ->get();

        if ($products->isEmpty()) {
            throw new \Exception('No hay productos disponibles');
        }

        $subtotal = 0;
        foreach ($products as $product) {
            $quantity = 1;
            $unitPrice = $product->sale_price;
            $itemSubtotal = $quantity * $unitPrice;
            $subtotal += $itemSubtotal;

            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'status' => 'pending'
            ]);
        }

        // Calcular totales (nota de venta sin IGV)
        $tax = 0; // Las notas de venta no incluyen IGV
        $total = $subtotal;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]);

        return $order;
    }
}
