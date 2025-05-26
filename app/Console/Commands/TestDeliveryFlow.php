<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\DeliveryOrder;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use Carbon\Carbon;

class TestDeliveryFlow extends Command
{
    protected $signature = 'delivery:test-flow {--reset}';
    protected $description = 'Probar el flujo completo de delivery desde orden hasta entrega';

    public function handle()
    {
        $reset = $this->option('reset');

        if ($reset) {
            $this->resetTestData();
        }

        $this->info("🚚 PROBANDO FLUJO COMPLETO DE DELIVERY");
        $this->line("");

        try {
            // FASE 1: Crear orden de delivery
            $this->info("📋 FASE 1: CREANDO ORDEN DE DELIVERY");
            $order = $this->createDeliveryOrder();
            $this->line("");

            // FASE 2: Crear pedido de delivery
            $this->info("📋 FASE 2: REGISTRANDO INFORMACIÓN DE DELIVERY");
            $deliveryOrder = $this->createDeliveryInfo($order);
            $this->line("");

            // FASE 3: Asignar repartidor
            $this->info("📋 FASE 3: ASIGNANDO REPARTIDOR");
            $this->assignDeliveryPerson($deliveryOrder);
            $this->line("");

            // FASE 4: Marcar como en tránsito
            $this->info("📋 FASE 4: MARCANDO COMO EN TRÁNSITO");
            $this->markInTransit($deliveryOrder);
            $this->line("");

            // FASE 5: Entregar pedido
            $this->info("📋 FASE 5: ENTREGANDO PEDIDO");
            $this->deliverOrder($deliveryOrder);
            $this->line("");

            // FASE 6: Mostrar resumen final
            $this->info("📋 FASE 6: RESUMEN FINAL");
            $this->showFinalSummary($deliveryOrder);

        } catch (\Exception $e) {
            $this->error("❌ Error en el flujo de delivery: " . $e->getMessage());
            $this->line("📍 Archivo: " . $e->getFile());
            $this->line("📍 Línea: " . $e->getLine());
            return 1;
        }

        return 0;
    }

    private function resetTestData(): void
    {
        $this->info("🔄 Limpiando datos de prueba anteriores...");

        // Eliminar órdenes de prueba
        Order::where('service_type', 'delivery')
            ->where('notes', 'like', '%PRUEBA DELIVERY%')
            ->delete();

        $this->line("✅ Datos de prueba eliminados");
    }

    private function createDeliveryOrder(): Order
    {
        // Buscar o crear cliente
        $customer = Customer::firstOrCreate(
            ['document_number' => '12345678'],
            [
                'document_type' => 'dni',
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@email.com',
                'phone' => '987654321',
                'address' => 'Av. Los Olivos 123, San Isidro'
            ]
        );

        // Buscar empleado para registrar la orden
        $employee = Employee::first();
        if (!$employee) {
            throw new \Exception('No hay empleados registrados. Crea al menos un empleado primero.');
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
            'notes' => 'PRUEBA DELIVERY - Orden de prueba para testing',
            'billed' => false
        ]);

        // Agregar productos a la orden
        $products = Product::where('product_type', 'sale_item')
            ->where('active', true)
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            throw new \Exception('No hay productos disponibles para la orden');
        }

        $subtotal = 0;
        foreach ($products as $product) {
            $quantity = rand(1, 2);
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

            $this->line("   ✅ {$product->name}: {$quantity} x S/ {$unitPrice} = S/ {$itemSubtotal}");
        }

        // Calcular totales
        $tax = $subtotal * 0.18;
        $total = $subtotal + $tax;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ]);

        $this->line("✅ Orden creada: #{$order->id}");
        $this->line("   👤 Cliente: {$customer->name}");
        $this->line("   📱 Teléfono: {$customer->phone}");
        $this->line("   💰 Total: S/ " . number_format($total, 2));
        $this->line("   📦 Productos: " . $products->count());

        return $order;
    }

    private function createDeliveryInfo(Order $order): DeliveryOrder
    {
        $deliveryOrder = DeliveryOrder::create([
            'order_id' => $order->id,
            'delivery_address' => 'Av. Los Olivos 123, San Isidro, Lima',
            'delivery_references' => 'Casa de dos pisos, portón negro, timbre rojo',
            'status' => DeliveryOrder::STATUS_PENDING,
            'estimated_delivery_time' => null // Se asignará cuando se asigne repartidor
        ]);

        $this->line("✅ Información de delivery registrada: #{$deliveryOrder->id}");
        $this->line("   📍 Dirección: {$deliveryOrder->delivery_address}");
        $this->line("   📝 Referencias: {$deliveryOrder->delivery_references}");
        $this->line("   📊 Estado: " . $deliveryOrder->getStatusInfo()['text']);

        return $deliveryOrder;
    }

    private function assignDeliveryPerson(DeliveryOrder $deliveryOrder): void
    {
        // Buscar repartidores disponibles
        $deliveryPersons = Employee::all();

        if ($deliveryPersons->isEmpty()) {
            throw new \Exception('No hay empleados disponibles para asignar como repartidores');
        }

        $deliveryPerson = $deliveryPersons->first();

        $success = $deliveryOrder->assignDeliveryPerson($deliveryPerson);

        if ($success) {
            $deliveryOrder->refresh();
            $this->line("✅ Repartidor asignado: {$deliveryPerson->full_name}");
            $this->line("   📊 Estado: " . $deliveryOrder->getStatusInfo()['text']);
            $this->line("   ⏰ Tiempo estimado: " . $deliveryOrder->estimated_delivery_time->format('H:i'));
        } else {
            throw new \Exception('No se pudo asignar el repartidor');
        }
    }

    private function markInTransit(DeliveryOrder $deliveryOrder): void
    {
        $success = $deliveryOrder->markAsInTransit();

        if ($success) {
            $deliveryOrder->refresh();
            $this->line("✅ Pedido marcado como en tránsito");
            $this->line("   📊 Estado: " . $deliveryOrder->getStatusInfo()['text']);
            $this->line("   🚚 Repartidor: " . $deliveryOrder->deliveryPerson->full_name);
        } else {
            throw new \Exception('No se pudo marcar como en tránsito');
        }
    }

    private function deliverOrder(DeliveryOrder $deliveryOrder): void
    {
        $success = $deliveryOrder->markAsDelivered();

        if ($success) {
            $deliveryOrder->refresh();
            $this->line("✅ Pedido entregado exitosamente");
            $this->line("   📊 Estado: " . $deliveryOrder->getStatusInfo()['text']);
            $this->line("   ⏰ Hora de entrega: " . $deliveryOrder->actual_delivery_time->format('H:i:s'));
            $this->line("   📋 Estado de orden: " . $deliveryOrder->order->status);
        } else {
            throw new \Exception('No se pudo marcar como entregado');
        }
    }

    private function showFinalSummary(DeliveryOrder $deliveryOrder): void
    {
        $deliveryOrder->refresh();
        $order = $deliveryOrder->order;

        $this->line("🎯 RESUMEN FINAL DEL DELIVERY:");
        $this->line("");
        $this->line("📦 Orden #{$order->id}");
        $this->line("   👤 Cliente: {$order->customer->name}");
        $this->line("   📱 Teléfono: {$order->customer->phone}");
        $this->line("   💰 Total: S/ " . number_format($order->total, 2));
        $this->line("");
        $this->line("🚚 Delivery #{$deliveryOrder->id}");
        $this->line("   📍 Dirección: {$deliveryOrder->delivery_address}");
        $this->line("   👨‍💼 Repartidor: " . $deliveryOrder->deliveryPerson->full_name);
        $this->line("   📊 Estado final: " . $deliveryOrder->getStatusInfo()['text']);
        $this->line("   ⏰ Tiempo total: " . $deliveryOrder->created_at->diffForHumans($deliveryOrder->actual_delivery_time, true));
        $this->line("");

        $this->info("🔄 FLUJO COMPLETADO EXITOSAMENTE:");
        $this->line("   1. ✅ Orden de delivery creada");
        $this->line("   2. ✅ Información de delivery registrada");
        $this->line("   3. ✅ Repartidor asignado");
        $this->line("   4. ✅ Marcado como en tránsito");
        $this->line("   5. ✅ Pedido entregado");
        $this->line("   6. ✅ Orden completada");

        $this->line("");
        $this->info("📊 Comandos para revisar:");
        $this->line("   php artisan delivery:list-active");
        $this->line("   php artisan delivery:show {$deliveryOrder->id}");
    }
}
