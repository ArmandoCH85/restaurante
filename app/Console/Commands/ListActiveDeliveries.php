<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeliveryOrder;

class ListActiveDeliveries extends Command
{
    protected $signature = 'delivery:list-active';
    protected $description = 'Listar todos los pedidos de delivery activos';

    public function handle()
    {
        $deliveries = DeliveryOrder::with(['order.customer', 'deliveryPerson'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        if ($deliveries->isEmpty()) {
            $this->info('📭 No hay pedidos de delivery activos');
            return 0;
        }

        $this->info("🚚 PEDIDOS DE DELIVERY ACTIVOS ({$deliveries->count()})");
        $this->line("");

        foreach ($deliveries as $delivery) {
            $statusInfo = $delivery->getStatusInfo();
            $this->line("📦 Delivery #{$delivery->id} | Orden #{$delivery->order->id}");
            $this->line("   👤 Cliente: {$delivery->order->customer->name}");
            $this->line("   📱 Teléfono: {$delivery->order->customer->phone}");
            $this->line("   📍 Dirección: {$delivery->delivery_address}");
            $this->line("   📊 Estado: {$statusInfo['text']}");

            if ($delivery->deliveryPerson) {
                $this->line("   👨‍💼 Repartidor: {$delivery->deliveryPerson->full_name}");
            }

            if ($delivery->estimated_delivery_time) {
                $this->line("   ⏰ Tiempo estimado: {$delivery->estimated_delivery_time->format('H:i')}");
            }

            $this->line("   🕐 Creado: {$delivery->elapsed_time}");
            $this->line("   " . str_repeat("─", 50));
        }

        return 0;
    }
}
