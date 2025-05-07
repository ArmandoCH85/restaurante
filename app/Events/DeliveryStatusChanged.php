<?php

namespace App\Events;

use App\Models\DeliveryOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deliveryOrder;
    public $previousStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(DeliveryOrder $deliveryOrder, string $previousStatus = null)
    {
        $this->deliveryOrder = $deliveryOrder;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('delivery-orders'),
            new PrivateChannel('delivery-order.' . $this->deliveryOrder->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->deliveryOrder->id,
            'order_id' => $this->deliveryOrder->order_id,
            'status' => $this->deliveryOrder->status,
            'previous_status' => $this->previousStatus,
            'delivery_address' => $this->deliveryOrder->delivery_address,
            'delivery_person_id' => $this->deliveryOrder->delivery_person_id,
            'delivery_person_name' => $this->deliveryOrder->deliveryPerson ? $this->deliveryOrder->deliveryPerson->full_name : null,
            'estimated_delivery_time' => $this->deliveryOrder->estimated_delivery_time ? $this->deliveryOrder->estimated_delivery_time->format('Y-m-d H:i:s') : null,
            'actual_delivery_time' => $this->deliveryOrder->actual_delivery_time ? $this->deliveryOrder->actual_delivery_time->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->deliveryOrder->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'delivery.status.changed';
    }
}
