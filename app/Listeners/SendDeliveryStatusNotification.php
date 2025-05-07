<?php

namespace App\Listeners;

use App\Events\DeliveryStatusChanged;
use App\Notifications\DeliveryStatusChanged as DeliveryStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDeliveryStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DeliveryStatusChanged $event): void
    {
        $deliveryOrder = $event->deliveryOrder;
        $order = $deliveryOrder->order;
        
        // Solo enviar notificación si hay un cliente asociado a la orden
        if ($order && $order->customer) {
            $customer = $order->customer;
            
            // Enviar notificación al cliente
            $customer->notify(new DeliveryStatusChangedNotification(
                $deliveryOrder,
                $event->previousStatus
            ));
        }
    }
}
