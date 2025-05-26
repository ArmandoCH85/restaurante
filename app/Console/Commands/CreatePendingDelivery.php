<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\DeliveryOrder;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;

class CreatePendingDelivery extends Command
{
    protected $signature = 'delivery:create-pending';
    protected $description = 'Crear un pedido de delivery pendiente para demostración';

    public function handle()
    {
        $this->info("🚚 CREANDO PEDIDO DE DELIVERY PENDIENTE");
        $this->line("");

        try {
            // Crear cliente
            $customer = Customer::firstOrCreate(
                ['document_number' => '87654321'],
                [
                    'document_type' => 'dni',
                    'name' => 'María García',
                    'email' => 'maria.garcia@email.com',
                    'phone' => '912345678',
                    'address' => 'Jr. Las Flores 456, Miraflores'
                ]
            );

            // Buscar empleado
            $employee = Employee::first();
            if (!$employee) {
                throw new \Exception('No hay empleados registrados');
            }

            // Crear la orden
            $order = Order::create([
                'service_type' => 'delivery',
                'customer_id' => $customer->id,
                'employee_id' => $employee->id,
                'order_datetime' => now(),
                'status' => Order::STATUS_OPEN,
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'notes' => 'DEMO DELIVERY - Pedido pendiente para demostración',
                'billed' => false
            ]);

            // Agregar productos
            $products = Product::where('product_type', 'sale_item')
                ->where('active', true)
                ->take(2)
                ->get();

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

                $this->line("   ✅ {$product->name}: {$quantity} x S/ {$unitPrice}");
            }

            // Calcular totales
            $tax = $subtotal * 0.18;
            $total = $subtotal + $tax;

            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);

            // Crear información de delivery (PENDIENTE)
            $deliveryOrder = DeliveryOrder::create([
                'order_id' => $order->id,
                'delivery_address' => 'Jr. Las Flores 456, Miraflores, Lima',
                'delivery_references' => 'Edificio azul, departamento 301, intercomunicador',
                'status' => DeliveryOrder::STATUS_PENDING,
                'estimated_delivery_time' => null
            ]);

            $this->line("");
            $this->info("✅ PEDIDO DE DELIVERY PENDIENTE CREADO:");
            $this->line("📦 Orden #{$order->id}");
            $this->line("🚚 Delivery #{$deliveryOrder->id}");
            $this->line("👤 Cliente: {$customer->name}");
            $this->line("📱 Teléfono: {$customer->phone}");
            $this->line("📍 Dirección: {$deliveryOrder->delivery_address}");
            $this->line("💰 Total: S/ " . number_format($total, 2));
            $this->line("📊 Estado: " . $deliveryOrder->getStatusInfo()['text']);

            $this->line("");
            $this->info("🔧 Comandos para gestionar:");
            $this->line("   php artisan delivery:list-active");
            $this->line("   php artisan delivery:show {$deliveryOrder->id}");

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
