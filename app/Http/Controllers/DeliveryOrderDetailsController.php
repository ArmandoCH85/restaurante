<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveryOrderDetailsController extends Controller
{
    /**
     * Muestra los detalles de un pedido de delivery
     */
    public function show($orderId)
    {
        try {
            // Registrar información para depuración
            Log::info('DeliveryOrderDetailsController::show - Inicio', [
                'orderId' => $orderId,
                'user_id' => Auth::user() ? Auth::user()->id : 'no_user',
                'name' => Auth::user() ? Auth::user()->name : 'no_name',
                'roles' => Auth::user() ? Auth::user()->roles->pluck('name')->toArray() : [],
                'is_authenticated' => Auth::check() ? 'Sí' : 'No'
            ]);
            
            // Cargar la orden con sus relaciones
            $order = Order::with(['orderDetails.product', 'customer', 'deliveryOrder.deliveryPerson'])
                ->findOrFail($orderId);
                
            // Registrar información de la orden
            Log::info('DeliveryOrderDetailsController::show - Orden cargada', [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'has_delivery_order' => $order->deliveryOrder ? 'Sí' : 'No'
            ]);

            // Cargar el pedido de delivery asociado
            $deliveryOrder = $order->deliveryOrder;
            
            if (!$deliveryOrder) {
                Log::error('DeliveryOrderDetailsController::show - No se encontró el pedido de delivery asociado', [
                    'order_id' => $order->id
                ]);
                
                return redirect()->route('tables.map')->with('error', 'No se encontró el pedido de delivery asociado');
            }

            // Verificar si el usuario tiene permiso para ver este pedido
            $user = Auth::user();
            $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
            
            Log::info('DeliveryOrderDetailsController::show - Verificando permisos', [
                'user_id' => $user ? $user->id : 'no_user',
                'is_delivery_person' => $isDeliveryPerson ? 'Sí' : 'No',
                'delivery_order_id' => $deliveryOrder->id,
                'delivery_person_id' => $deliveryOrder->delivery_person_id
            ]);

            if ($isDeliveryPerson) {
                $employee = Employee::where('user_id', $user->id)->first();
                
                Log::info('DeliveryOrderDetailsController::show - Información del empleado', [
                    'employee_id' => $employee ? $employee->id : 'no_employee',
                    'employee_name' => $employee ? $employee->full_name : 'no_name'
                ]);
                
                // Si es un repartidor, solo puede ver sus propios pedidos
                if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                    // Registrar intento de acceso no autorizado
                    Log::warning('Intento de acceso no autorizado a pedido de delivery', [
                        'user_id' => $user->id,
                        'delivery_order_id' => $deliveryOrder->id,
                        'employee_id' => $employee ? $employee->id : 'no_employee',
                        'delivery_person_id' => $deliveryOrder->delivery_person_id
                    ]);
                    
                    // Redirigir a la lista de pedidos
                    return redirect()->route('tables.map')->with('error', 'No tienes permiso para ver este pedido');
                }
            }
            
            Log::info('DeliveryOrderDetailsController::show - Renderizando vista', [
                'order_id' => $order->id,
                'delivery_order_id' => $deliveryOrder->id
            ]);
            
            return view('delivery.order-details', [
                'order' => $order,
                'deliveryOrder' => $deliveryOrder
            ]);
            
        } catch (\Exception $e) {
            Log::error('DeliveryOrderDetailsController::show - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'orderId' => $orderId
            ]);
            
            return redirect()->route('tables.map')->with('error', 'Error al cargar el pedido: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza el estado de un pedido de delivery
     */
    public function updateStatus(Request $request, $deliveryOrderId)
    {
        try {
            $newStatus = $request->input('status');
            
            Log::info('DeliveryOrderDetailsController::updateStatus - Inicio', [
                'deliveryOrderId' => $deliveryOrderId,
                'newStatus' => $newStatus,
                'user_id' => Auth::user() ? Auth::user()->id : 'no_user'
            ]);
            
            $deliveryOrder = DeliveryOrder::findOrFail($deliveryOrderId);
            
            // Verificar si el usuario tiene permiso para actualizar este pedido
            $user = Auth::user();
            $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;
            $employee = $isDeliveryPerson ? Employee::where('user_id', $user->id)->first() : null;
            
            // Para estados "in_transit" y "delivered", solo el repartidor asignado puede actualizar
            if (in_array($newStatus, ['in_transit', 'delivered'])) {
                if (!$isDeliveryPerson) {
                    return redirect()->back()->with('error', 'Solo los repartidores pueden actualizar el estado de entrega');
                }
                
                if (!$employee || $deliveryOrder->delivery_person_id !== $employee->id) {
                    return redirect()->back()->with('error', 'Solo el repartidor asignado puede actualizar este pedido');
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
                    // Cancelar el pedido
                    $reason = $request->input('reason');
                    $success = $deliveryOrder->cancel($reason);
                    break;
            }
            
            if ($success) {
                // Disparar evento de cambio de estado
                event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));
                
                return redirect()->route('tables.map')->with('success', 'Estado del pedido actualizado correctamente');
            } else {
                return redirect()->back()->with('error', 'No se pudo actualizar el estado del pedido');
            }
            
        } catch (\Exception $e) {
            Log::error('DeliveryOrderDetailsController::updateStatus - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'deliveryOrderId' => $deliveryOrderId
            ]);
            
            return redirect()->back()->with('error', 'Error al actualizar el estado del pedido: ' . $e->getMessage());
        }
    }
}
