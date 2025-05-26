<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliveryOrder;

class ShowDeliveryDetails extends Command
{
    protected $signature = 'delivery:show {delivery_id}';
    protected $description = 'Mostrar detalles completos de un pedido de delivery';

    public function handle()
    {
        $deliveryId = $this->argument('delivery_id');

        $delivery = DeliveryOrder::with(['order.orderDetails.product', 'order.customer', 'deliveryPerson'])
            ->find($deliveryId);

        if (!$delivery) {
            $this->error("Delivery #{$deliveryId} no encontrado");
            return 1;
        }

        $this->showDeliveryInfo($delivery);
        $this->showOrderDetails($delivery->order);
        $this->showTimeline($delivery);

        return 0;
    }

    private function showDeliveryInfo(DeliveryOrder $delivery): void
    {
        $statusInfo = $delivery->getStatusInfo();

        $this->info("🚚 INFORMACIÓN DEL DELIVERY #{$delivery->id}");
        $this->line("");
        $this->line("📊 Estado: {$statusInfo['text']}");
        $this->line("📍 Dirección: {$delivery->delivery_address}");
        $this->line("📝 Referencias: {$delivery->delivery_references}");

        if ($delivery->deliveryPerson) {
            $this->line("👨‍💼 Repartidor: {$delivery->deliveryPerson->full_name}");
        }

        if ($delivery->estimated_delivery_time) {
            $this->line("⏰ Tiempo estimado: {$delivery->estimated_delivery_time->format('d/m/Y H:i')}");
        }

        if ($delivery->actual_delivery_time) {
            $this->line("✅ Entregado: {$delivery->actual_delivery_time->format('d/m/Y H:i:s')}");
        }

        if ($delivery->cancellation_reason) {
            $this->line("❌ Razón cancelación: {$delivery->cancellation_reason}");
        }

        $this->line("");
    }

    private function showOrderDetails($order): void
    {
        $this->info("📦 DETALLES DE LA ORDEN #{$order->id}");
        $this->line("");
        $this->line("👤 Cliente: {$order->customer->name}");
        $this->line("📱 Teléfono: {$order->customer->phone}");
        $this->line("📧 Email: {$order->customer->email}");
        $this->line("🆔 Documento: {$order->customer->document_type} {$order->customer->document_number}");
        $this->line("📅 Fecha orden: {$order->order_datetime->format('d/m/Y H:i:s')}");
        $this->line("💰 Total: S/ " . number_format($order->total, 2));
        $this->line("");

        $this->info("🍽️ PRODUCTOS ORDENADOS:");
        foreach ($order->orderDetails as $detail) {
            $this->line("   • {$detail->product->name}");
            $this->line("     Cantidad: {$detail->quantity}");
            $this->line("     Precio unitario: S/ " . number_format($detail->unit_price, 2));
            $this->line("     Subtotal: S/ " . number_format($detail->subtotal, 2));
            $this->line("");
        }
    }

    private function showTimeline(DeliveryOrder $delivery): void
    {
        $this->info("⏱️ LÍNEA DE TIEMPO:");
        $this->line("");

        $this->line("📅 {$delivery->created_at->format('d/m/Y H:i:s')} - Pedido creado");

        if ($delivery->assigned_at) {
            $this->line("👨‍💼 {$delivery->assigned_at->format('d/m/Y H:i:s')} - Repartidor asignado");
        }

        if ($delivery->picked_up_at) {
            $this->line("📦 {$delivery->picked_up_at->format('d/m/Y H:i:s')} - Pedido recogido");
        }

        if ($delivery->delivered_at) {
            $this->line("✅ {$delivery->delivered_at->format('d/m/Y H:i:s')} - Pedido entregado");
        }

        if ($delivery->isCancelled()) {
            $this->line("❌ {$delivery->updated_at->format('d/m/Y H:i:s')} - Pedido cancelado");
        }

        $this->line("");

        if ($delivery->isDelivered()) {
            $totalTime = $delivery->created_at->diffForHumans($delivery->actual_delivery_time, true);
            $this->line("⏱️ Tiempo total de entrega: {$totalTime}");
        } elseif (!$delivery->isCancelled()) {
            $elapsedTime = $delivery->created_at->diffForHumans();
            $this->line("⏱️ Tiempo transcurrido: {$elapsedTime}");
        }
    }
}
