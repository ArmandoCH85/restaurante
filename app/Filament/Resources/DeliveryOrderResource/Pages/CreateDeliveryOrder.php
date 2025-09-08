<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

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
        return "😅 Ocurrió un problema al crear el pedido de delivery. Revisa los datos e intenta de nuevo.";
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('¡Pedido de delivery creado!')
                ->body('El pedido de delivery ha sido registrado correctamente 🚚')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al crear el pedido de delivery')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterCreate de DeliveryOrder: ' . $e->getMessage(), [
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

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de DeliveryOrder: ' . $e->getMessage(), [
                'delivery_order_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
