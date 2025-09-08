<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key) - cuando el cliente está siendo usado
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'orders')) {
                return "🚫 No se puede eliminar porque este cliente tiene pedidos realizados. Primero elimina los pedidos relacionados.";
            }
            if (str_contains($errorMessage, 'delivery_orders')) {
                return "🚫 No se puede eliminar porque este cliente tiene pedidos de delivery. Primero elimina los pedidos de delivery.";
            }
            return "🚫 No se puede eliminar porque este cliente está siendo usado en otras partes del sistema.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'document_number')) {
                return "🆔 Ya existe otro cliente con ese número de documento. Verifica el DNI/RUC.";
            }
            if (str_contains($errorMessage, 'phone')) {
                return "📞 Ya existe otro cliente con ese número de teléfono. Usa otro teléfono.";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 Ya existe otro cliente con ese correo electrónico. Usa otro email.";
            }
            if (str_contains($errorMessage, 'name')) {
                return "👤 Ya existe otro cliente con ese nombre. Agrega más detalles para diferenciarlo.";
            }
            return "📝 Ya existe otro cliente con esos datos. Revisa y cambia los valores.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'name')) {
                return "👤 El nombre del cliente es obligatorio. Completa este campo.";
            }
            if (str_contains($errorMessage, 'phone')) {
                return "📞 El teléfono es obligatorio. Completa este campo.";
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
                    // Verificar si el cliente puede ser eliminado
                    $customer = $this->record;

                    // Verificar si tiene pedidos
                    if ($customer->orders()->exists()) {
                        throw new \Exception("Este cliente no puede ser eliminado porque tiene pedidos realizados.");
                    }

                    // Verificar si tiene pedidos de delivery
                    if ($customer->deliveryOrders()->exists()) {
                        throw new \Exception("Este cliente no puede ser eliminado porque tiene pedidos de delivery.");
                    }
                })
                ->action(function () {
                    try {
                        $customer = $this->record;
                        $customerName = $customer->name;

                        $customer->delete();

                        Notification::make()
                            ->title('¡Cliente eliminado!')
                            ->body("El cliente '{$customerName}' ha sido eliminado correctamente ✅")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.customers.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('No se puede eliminar el cliente')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al eliminar cliente: ' . $e->getMessage(), [
                            'customer_id' => $this->record->id,
                            'customer_name' => $this->record->name,
                            'error_code' => $e->getCode(),
                            'error_message' => $e->getMessage()
                        ]);

                    } catch (Exception $e) {
                        $friendlyMessage = "🚫 " . $e->getMessage();

                        Notification::make()
                            ->title('No se puede eliminar el cliente')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al eliminar cliente: ' . $e->getMessage(), [
                            'customer_id' => $this->record->id,
                            'customer_name' => $this->record->name
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            Notification::make()
                ->title('¡Cliente actualizado!')
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

            \Illuminate\Support\Facades\Log::error('Error en afterSave de Customer: ' . $e->getMessage(), [
                'customer_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterSave de Customer: ' . $e->getMessage(), [
                'customer_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
