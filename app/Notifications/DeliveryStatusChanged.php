<?php

namespace App\Notifications;

use App\Models\DeliveryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deliveryOrder;
    protected $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(DeliveryOrder $deliveryOrder, string $previousStatus = null)
    {
        $this->deliveryOrder = $deliveryOrder;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'pending' => 'Su pedido ha sido recibido y está pendiente de asignación.',
            'assigned' => 'Su pedido ha sido asignado a un repartidor y está siendo preparado.',
            'in_transit' => 'Su pedido está en camino a su dirección.',
            'delivered' => 'Su pedido ha sido entregado. ¡Gracias por su compra!',
            'cancelled' => 'Su pedido ha sido cancelado.'
        ];

        $message = $statusMessages[$this->deliveryOrder->status] ?? 'El estado de su pedido ha cambiado.';
        $subject = 'Actualización de su pedido #' . $this->deliveryOrder->order_id;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . $notifiable->name)
            ->line($message);

        // Si el pedido está en tránsito, añadir información de tiempo estimado
        if ($this->deliveryOrder->status === 'in_transit' && $this->deliveryOrder->estimated_delivery_time) {
            $estimatedTime = $this->deliveryOrder->estimated_delivery_time->format('H:i');
            $mail->line('Tiempo estimado de entrega: ' . $estimatedTime);
        }

        // Si el pedido ha sido asignado, añadir información del repartidor
        if ($this->deliveryOrder->status === 'assigned' && $this->deliveryOrder->deliveryPerson) {
            $mail->line('Repartidor asignado: ' . $this->deliveryOrder->deliveryPerson->full_name);
        }

        // Si el pedido ha sido entregado, añadir enlace para valorar el servicio
        if ($this->deliveryOrder->status === 'delivered') {
            $mail->action('Valorar su experiencia', url('/feedback/' . $this->deliveryOrder->id));
        }

        $mail->line('Gracias por confiar en nosotros.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'delivery_order_id' => $this->deliveryOrder->id,
            'order_id' => $this->deliveryOrder->order_id,
            'status' => $this->deliveryOrder->status,
            'previous_status' => $this->previousStatus,
            'delivery_person' => $this->deliveryOrder->deliveryPerson ? $this->deliveryOrder->deliveryPerson->full_name : null,
            'estimated_delivery_time' => $this->deliveryOrder->estimated_delivery_time ? $this->deliveryOrder->estimated_delivery_time->format('Y-m-d H:i:s') : null,
            'actual_delivery_time' => $this->deliveryOrder->actual_delivery_time ? $this->deliveryOrder->actual_delivery_time->format('Y-m-d H:i:s') : null,
        ];
    }
}
