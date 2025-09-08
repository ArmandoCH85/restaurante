<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Exception;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

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
            if (str_contains($errorMessage, 'table_id')) {
                return "🚫 La mesa seleccionada no existe. Elige otra mesa.";
            }
            if (str_contains($errorMessage, 'employee_id')) {
                return "🚫 El empleado seleccionado no existe. Verifica que esté registrado.";
            }
            if (str_contains($errorMessage, 'cash_register_id')) {
                return "🚫 La caja registradora seleccionada no existe. Abre una caja primero.";
            }
            return "🚫 Hay datos relacionados que no existen. Revisa las selecciones.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'customer_id') && str_contains($errorMessage, 'dine_in')) {
                return "👤 Para pedidos en mesa es obligatorio seleccionar un cliente. Elige un cliente.";
            }
            if (str_contains($errorMessage, 'table_id')) {
                return "🍽️ Es obligatorio seleccionar una mesa para pedidos en restaurante.";
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
        return "😅 Ocurrió un problema al crear el pedido. Revisa los datos e intenta de nuevo.";
    }

    public function mount(): void
    {
        parent::mount();

        // Pre-completar formulario si viene table_id
        if (request()->has('table_id')) {
            $this->form->fill([
                'table_id' => request()->get('table_id'),
                'service_type' => 'dine_in',
                'employee_id' => Auth::id(),
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // Obtener la caja registradora activa
            $activeCashRegister = \App\Models\CashRegister::getOpenRegister();

            if (!$activeCashRegister) {
                Notification::make()
                    ->title('Caja cerrada')
                    ->body('💰 No hay una caja registradora abierta. Abre una caja primero para poder crear pedidos.')
                    ->danger()
                    ->persistent()
                    ->send();

                // Lanzar excepción para prevenir la creación
                throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
            }

            // Establecer valores por defecto
            $data['employee_id'] = $data['employee_id'] ?? Auth::id();
            $data['order_datetime'] = now();
            $data['status'] = 'open';
            $data['subtotal'] = 0;
            $data['tax'] = 0;
            $data['total'] = 0;
            $data['discount'] = $data['discount'] ?? 0;
            $data['billed'] = false;
            $data['cash_register_id'] = $activeCashRegister->id;

            // Si viene table_id desde el mapa de mesas, pre-asignar
            if (request()->has('table_id') && !isset($data['table_id'])) {
                $data['table_id'] = request()->get('table_id');
                $data['service_type'] = 'dine_in';
            }

            return $data;

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            // Manejar error específico de caja cerrada
            if (str_contains($errorMessage, 'caja registradora abierta')) {
                // El error ya fue mostrado por la notificación, solo loggear
                \Illuminate\Support\Facades\Log::info('Intento de crear pedido sin caja abierta: ' . $errorMessage);
            } else {
                // Otros errores inesperados
                Notification::make()
                    ->title('Problema al preparar el pedido')
                    ->body('😅 Ocurrió un problema al preparar los datos del pedido. Intenta de nuevo.')
                    ->danger()
                    ->send();

                \Illuminate\Support\Facades\Log::error('Error en mutateFormDataBeforeCreate de Order: ' . $e->getMessage());
            }

            // Devolver los datos originales si hay error
            return $data;
        }
    }

    protected function afterCreate(): void
    {
        try {
            // Recalcular totales después de crear
            $this->record->recalculateTotals();

            // Si hay una mesa asignada, marcarla como ocupada
            if ($this->record->table_id && $this->record->service_type === 'dine_in') {
                $table = $this->record->table;
                if ($table && $table->isAvailable()) {
                    $table->update(['status' => 'occupied']);
                }
            }

            Notification::make()
                ->title('¡Pedido creado!')
                ->body('El pedido ha sido registrado correctamente ✅')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al crear el pedido')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterCreate de Order: ' . $e->getMessage(), [
                'order_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado al finalizar el pedido. El pedido fue creado pero puede haber problemas con los totales.')
                ->warning()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Order: ' . $e->getMessage(), [
                'order_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
