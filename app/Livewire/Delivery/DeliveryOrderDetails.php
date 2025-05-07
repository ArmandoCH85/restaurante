<?php

namespace App\Livewire\Delivery;

use Livewire\Component;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveryOrderDetails extends Component
{
    public $orderId;
    public $order;
    public $deliveryOrder;

    public function mount($orderId)
    {
        // Registrar información para depuración
        Log::info('DeliveryOrderDetails::mount', [
            'orderId' => $orderId,
            'user_id' => Auth::user() ? Auth::user()->id : 'no_user',
            'name' => Auth::user() ? Auth::user()->name : 'no_name',
            'roles' => Auth::user() ? Auth::user()->roles->pluck('name')->toArray() : [],
            'is_authenticated' => Auth::check() ? 'Sí' : 'No'
        ]);

        $this->orderId = $orderId;
        $this->loadOrder();
    }

    public function loadOrder()
    {
        try {
            // Registrar información para depuración
            Log::info('DeliveryOrderDetails::loadOrder - Inicio', [
                'orderId' => $this->orderId,
                'user_id' => Auth::user() ? Auth::user()->id : 'no_user'
            ]);

            // Cargar la orden con sus relaciones
            $this->order = Order::with(['orderDetails.product', 'customer', 'deliveryOrder.deliveryPerson'])
                ->findOrFail($this->orderId);

            // Registrar información de la orden
            Log::info('DeliveryOrderDetails::loadOrder - Orden cargada', [
                'order_id' => $this->order->id,
                'order_status' => $this->order->status,
                'has_delivery_order' => $this->order->deliveryOrder ? 'Sí' : 'No'
            ]);

            // Cargar el pedido de delivery asociado
            $this->deliveryOrder = $this->order->deliveryOrder;

            if (!$this->deliveryOrder) {
                Log::error('DeliveryOrderDetails::loadOrder - No se encontró el pedido de delivery asociado', [
                    'order_id' => $this->order->id
                ]);

                return redirect()->route('tables.map')->with('error', 'No se encontró el pedido de delivery asociado');
            }

            // Verificar si el usuario tiene permiso para ver este pedido
            $user = Auth::user();
            $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

            Log::info('DeliveryOrderDetails::loadOrder - Verificando permisos', [
                'user_id' => $user ? $user->id : 'no_user',
                'is_delivery_person' => $isDeliveryPerson ? 'Sí' : 'No',
                'delivery_order_id' => $this->deliveryOrder->id,
                'delivery_person_id' => $this->deliveryOrder->delivery_person_id
            ]);

            if ($isDeliveryPerson) {
                $employee = \App\Models\Employee::where('user_id', $user->id)->first();

                Log::info('DeliveryOrderDetails::loadOrder - Información del empleado', [
                    'employee_id' => $employee ? $employee->id : 'no_employee',
                    'employee_name' => $employee ? $employee->full_name : 'no_name'
                ]);

                // Si es un repartidor, solo puede ver sus propios pedidos
                if (!$employee || $this->deliveryOrder->delivery_person_id !== $employee->id) {
                    // Registrar intento de acceso no autorizado
                    Log::warning('Intento de acceso no autorizado a pedido de delivery', [
                        'user_id' => $user->id,
                        'delivery_order_id' => $this->deliveryOrder->id,
                        'employee_id' => $employee ? $employee->id : 'no_employee',
                        'delivery_person_id' => $this->deliveryOrder->delivery_person_id
                    ]);

                    // Redirigir a la lista de pedidos
                    return redirect()->route('tables.map')->with('error', 'No tienes permiso para ver este pedido');
                }
            }

            Log::info('DeliveryOrderDetails::loadOrder - Finalizado correctamente');

        } catch (\Exception $e) {
            Log::error('DeliveryOrderDetails::loadOrder - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'orderId' => $this->orderId
            ]);

            return redirect()->route('tables.map')->with('error', 'Error al cargar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el estado de un pedido de delivery
     */
    public function updateDeliveryStatus($deliveryOrderId, string $newStatus): void
    {
        // Asegurarse de que $deliveryOrderId sea un entero
        if (is_array($deliveryOrderId)) {
            // Si es un array, tomamos el primer elemento
            $deliveryOrderId = $deliveryOrderId[0] ?? null;
        }

        // Convertir a entero
        $deliveryOrderId = (int) $deliveryOrderId;

        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);
        if (!$deliveryOrder) {
            $this->dispatch('notification', [
                'message' => "Pedido no encontrado",
                'type' => 'error'
            ]);
            return;
        }

        // Verificar si el usuario tiene permiso para actualizar este pedido
        $user = Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
        $employee = $isDeliveryPerson ? \App\Models\Employee::where('user_id', $user->id)->first() : null;

        // Para estados "in_transit" y "delivered", solo el repartidor asignado puede actualizar
        if (in_array($newStatus, ['in_transit', 'delivered'])) {
            if (!$isDeliveryPerson) {
                $this->dispatch('notification', [
                    'message' => "Solo los repartidores pueden actualizar el estado de entrega",
                    'type' => 'error'
                ]);
                return;
            }

            if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                $this->dispatch('notification', [
                    'message' => "Solo el repartidor asignado puede actualizar este pedido",
                    'type' => 'error'
                ]);
                return;
            }
        }

        $previousStatus = $deliveryOrder->status;
        $success = false;

        switch ($newStatus) {
            case 'in_transit':
                // Solo el repartidor asignado puede marcar como en tránsito
                $success = $deliveryOrder->markAsInTransit();
                break;

            case 'delivered':
                // Solo el repartidor asignado puede marcar como entregado
                $success = $deliveryOrder->markAsDelivered();
                break;

            case 'cancelled':
                // Mostrar modal para ingresar motivo de cancelación
                $this->dispatch('openCancelDeliveryModal', $deliveryOrderId);
                return;
        }

        if ($success) {
            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            // Recargar el pedido
            $this->loadOrder();

            $this->dispatch('notification', [
                'message' => "Estado del pedido actualizado a " . $this->getDeliveryStatusName($newStatus),
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notification', [
                'message' => "No se pudo actualizar el estado del pedido",
                'type' => 'error'
            ]);
        }
    }

    /**
     * Abre el modal para cancelar un pedido
     */
    public function openCancelModal($deliveryId): void
    {
        $this->dispatch('openCancelDeliveryModal', $deliveryId);
    }

    /**
     * Cancela un pedido de delivery
     */
    public function cancelDelivery($deliveryOrderId, ?string $reason = null): void
    {
        // Asegurarse de que $deliveryOrderId sea un entero
        if (is_array($deliveryOrderId)) {
            // Si es un array, tomamos el primer elemento
            $deliveryOrderId = $deliveryOrderId[0] ?? null;
        }

        // Convertir a entero
        $deliveryOrderId = (int) $deliveryOrderId;

        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);

        if (!$deliveryOrder) {
            $this->dispatch('notification', [
                'message' => "Pedido no encontrado",
                'type' => 'error'
            ]);
            return;
        }

        // Verificar si el usuario tiene permiso para cancelar este pedido
        $user = Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
        $employee = $isDeliveryPerson ? \App\Models\Employee::where('user_id', $user->id)->first() : null;

        // Si es un repartidor, solo puede cancelar sus propios pedidos
        if ($isDeliveryPerson) {
            if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                $this->dispatch('notification', [
                    'message' => "Solo puedes cancelar tus propios pedidos asignados",
                    'type' => 'error'
                ]);
                return;
            }
        }

        $previousStatus = $deliveryOrder->status;
        $success = $deliveryOrder->cancel($reason);

        if ($success) {
            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            // Recargar el pedido
            $this->loadOrder();

            $this->dispatch('notification', [
                'message' => "Pedido #{$deliveryOrder->order_id} cancelado",
                'type' => 'success'
            ]);
        } else {
            $this->dispatch('notification', [
                'message' => "No se pudo cancelar el pedido",
                'type' => 'error'
            ]);
        }
    }

    /**
     * Obtiene el nombre legible de un estado de delivery
     */
    public function getDeliveryStatusName(string $status): string
    {
        $statusNames = [
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        return $statusNames[$status] ?? $status;
    }

    public function render()
    {
        try {
            // Verificar si tenemos los datos necesarios
            if (!$this->order || !$this->deliveryOrder) {
                Log::error('DeliveryOrderDetails::render - Datos incompletos', [
                    'has_order' => $this->order ? 'Sí' : 'No',
                    'has_delivery_order' => $this->deliveryOrder ? 'Sí' : 'No',
                    'order_id' => $this->orderId
                ]);

                // Si no tenemos los datos necesarios, redirigir a la lista de pedidos
                return redirect()->route('tables.map')->with('error', 'No se encontraron los datos del pedido');
            }

            Log::info('DeliveryOrderDetails::render - Renderizando vista', [
                'order_id' => $this->order->id,
                'delivery_order_id' => $this->deliveryOrder->id
            ]);

            return view('livewire.delivery.delivery-order-details');

        } catch (\Exception $e) {
            Log::error('DeliveryOrderDetails::render - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // En caso de error, redirigir a la lista de pedidos
            return redirect()->route('tables.map')->with('error', 'Error al mostrar el pedido: ' . $e->getMessage());
        }
    }
}
