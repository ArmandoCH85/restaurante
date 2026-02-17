<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    /**
     * Convierte errores tecnicos de base de datos en mensajes simples para usuarios.
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'customer_id')) {
                return 'El cliente seleccionado no existe. Verifica que este registrado.';
            }
            if (str_contains($errorMessage, 'product_id')) {
                return 'Uno de los productos seleccionados no existe. Revisa la lista de productos.';
            }
            if (str_contains($errorMessage, 'delivery_person_id')) {
                return 'El repartidor seleccionado no existe. Elige otro repartidor.';
            }

            return 'Hay datos relacionados que no existen. Revisa las selecciones.';
        }

        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'customer_id')) {
                return 'Es obligatorio seleccionar un cliente para el delivery.';
            }
            if (str_contains($errorMessage, 'delivery_address')) {
                return 'La direccion de delivery es obligatoria.';
            }

            return 'Faltan completar campos obligatorios. Revisa el formulario.';
        }

        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return 'Problema de conexion. Espera unos segundos e intenta de nuevo.';
        }

        if ($errorCode == 1213) {
            return 'Los datos estan ocupados. Espera unos segundos e intenta nuevamente.';
        }

        return 'Ocurrio un problema al crear el pedido de delivery. Revisa los datos e intenta de nuevo.';
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('Pedido de delivery creado')
                ->body('El pedido fue registrado correctamente.')
                ->success()
                ->send();

            if (is_null($this->record->delivery_latitude) || is_null($this->record->delivery_longitude)) {
                Notification::make()
                    ->title('Sin geolocalizar')
                    ->body('Usa la accion Re-geocodificar para calcular latitud y longitud.')
                    ->warning()
                    ->send();
            }
        } catch (QueryException $e) {
            Notification::make()
                ->title('Problema al crear el pedido de delivery')
                ->body($this->getFriendlyErrorMessage($e))
                ->danger()
                ->persistent()
                ->send();
        } catch (Exception) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('Ocurrio un error inesperado. Intenta nuevamente.')
                ->danger()
                ->send();
        }
    }
}
