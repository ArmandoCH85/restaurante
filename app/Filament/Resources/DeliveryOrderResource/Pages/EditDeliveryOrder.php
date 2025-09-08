<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key) - cuando el pedido está siendo usado
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'delivery_orders')) {
                return "🚫 No se puede eliminar porque este pedido de delivery tiene registros relacionados. Primero elimina los registros relacionados.";
            }
            return "🚫 No se puede eliminar porque este pedido está siendo usado en otras partes del sistema.";
        }

        // Errores de clave foránea (foreign key)
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'customer_id')) {
                return "🚫 El cliente seleccionado no existe. Verifica que esté registrado correctamente.";
            }
            if (str_contains($errorMessage, 'product_id')) {
                return "🚫 Uno de los productos seleccionados no existe. Revisa la lista de productos.";
            }
            if (str_contains($errorMessage, 'delivery_person_id')) {
                return "🚫 El repartidor seleccionado no existe. Elige otro repartidor.";
            }
            return "🚫 Hay datos relacionados que no existen. Revisa las selecciones.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'customer_id')) {
                return "👤 Es obligatorio seleccionar un cliente para el delivery.";
            }
            if (str_contains($errorMessage, 'delivery_address')) {
                return "📍 La dirección de delivery es obligatoria. Completa la dirección.";
            }
            if (str_contains($errorMessage, 'delivery_phone')) {
                return "📞 El teléfono de delivery es obligatorio. Completa el teléfono.";
            }
            return "📝 Faltan completar algunos campos obligatorios. Revisa los marcados con asterisco (*).";
        }

        // Errores de conexión
        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return "🌐 Problema de conexión. Espera 10 segundos y vuelve a intentar.";
        }

        // Deadlock o bloqueo de datos
        if ($errorCode == 1213) {
            return "⏳ Los datos están ocupados. Cierra esta ventana, espera 5 segundos y abre de nuevo.";
        }

        // Error genérico
        return "😅 Ocurrió un problema al guardar los cambios. Revisa los datos e intenta de nuevo.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Verificar si el pedido de delivery puede ser eliminado
                    $deliveryOrder = $this->record;

                    // Verificar si tiene registros relacionados que impidan la eliminación
                    // Aquí puedes agregar validaciones específicas según el modelo DeliveryOrder
                })
                ->action(function () {
                    try {
                        $deliveryOrder = $this->record;
                        $orderNumber = $deliveryOrder->order_number ?? 'sin número';

                        $deliveryOrder->delete();

                        Notification::make()
                            ->title('¡Pedido de delivery eliminado!')
                            ->body("El pedido de delivery {$orderNumber} ha sido eliminado correctamente ✅")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.delivery-orders.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('No se puede eliminar el pedido de delivery')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al eliminar pedido de delivery: ' . $e->getMessage(), [
                            'delivery_order_id' => $this->record->id,
                            'order_number' => $this->record->order_number ?? null,
                            'error_code' => $e->getCode(),
                            'error_message' => $e->getMessage()
                        ]);

                    } catch (Exception $e) {
                        $friendlyMessage = "🚫 " . $e->getMessage();

                        Notification::make()
                            ->title('No se puede eliminar el pedido de delivery')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al eliminar pedido de delivery: ' . $e->getMessage(), [
                            'delivery_order_id' => $this->record->id,
                            'order_number' => $this->record->order_number ?? null
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            Notification::make()
                ->title('¡Pedido de delivery actualizado!')
                ->body('Los cambios han sido guardados correctamente ✅')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al guardar los cambios')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterSave de DeliveryOrder: ' . $e->getMessage(), [
                'delivery_order_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterSave de DeliveryOrder: ' . $e->getMessage(), [
                'delivery_order_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
