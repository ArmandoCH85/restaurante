<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Jobs\GeocodeDeliveryOrderJob;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\QueryException;

class EditDeliveryOrder extends EditRecord
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
            if (str_contains($errorMessage, 'delivery_orders')) {
                return 'No se puede eliminar porque este pedido de delivery tiene registros relacionados.';
            }

            if (str_contains($errorMessage, 'customer_id')) {
                return 'El cliente seleccionado no existe. Verifica que este registrado.';
            }

            if (str_contains($errorMessage, 'product_id')) {
                return 'Uno de los productos seleccionados no existe.';
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
            return 'Los datos estan ocupados. Intenta nuevamente en unos segundos.';
        }

        return 'Ocurrio un problema al guardar los cambios. Revisa los datos e intenta de nuevo.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('re_geocode')
                ->label('Re-geocodificar')
                ->icon('heroicon-o-map-pin')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Re-geocodificar direccion')
                ->modalDescription('Se encolara el proceso para obtener latitud y longitud con Nominatim.')
                ->action(function (): void {
                    GeocodeDeliveryOrderJob::dispatch($this->record->id, true)->afterCommit();

                    Notification::make()
                        ->title('Re-geocodificacion en proceso')
                        ->body('La direccion se procesara en segundos.')
                        ->info()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\DeleteAction::make()
                ->action(function () {
                    try {
                        $deliveryOrder = $this->record;
                        $orderNumber = $deliveryOrder->order_number ?? 'sin numero';

                        $deliveryOrder->delete();

                        Notification::make()
                            ->title('Pedido de delivery eliminado')
                            ->body("El pedido {$orderNumber} fue eliminado correctamente.")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.delivery-orders.index');
                    } catch (QueryException $e) {
                        Notification::make()
                            ->title('No se puede eliminar el pedido de delivery')
                            ->body($this->getFriendlyErrorMessage($e))
                            ->danger()
                            ->persistent()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('No se puede eliminar el pedido de delivery')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            Notification::make()
                ->title('Pedido de delivery actualizado')
                ->body('Los cambios fueron guardados correctamente.')
                ->success()
                ->send();

            if (is_null($this->record->delivery_latitude) || is_null($this->record->delivery_longitude)) {
                Notification::make()
                    ->title('Sin geolocalizar')
                    ->body('Si corregiste direccion, usa la accion Re-geocodificar.')
                    ->warning()
                    ->send();
            }
        } catch (QueryException $e) {
            Notification::make()
                ->title('Problema al guardar los cambios')
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
