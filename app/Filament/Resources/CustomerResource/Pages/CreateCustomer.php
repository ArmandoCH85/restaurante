<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'document_number')) {
                return "🆔 Ya existe un cliente con ese número de documento. Verifica el DNI/RUC.";
            }
            if (str_contains($errorMessage, 'phone')) {
                return "📞 Ya existe un cliente con ese número de teléfono. Usa otro teléfono.";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 Ya existe un cliente con ese correo electrónico. Usa otro email.";
            }
            if (str_contains($errorMessage, 'name')) {
                return "👤 Ya existe un cliente con ese nombre. Agrega más detalles para diferenciarlo.";
            }
            return "📝 Ya existe un cliente con esos datos. Revisa y cambia los valores.";
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
        return "😅 Ocurrió un problema al guardar el cliente. Revisa los datos e intenta de nuevo.";
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('¡Cliente creado!')
                ->body('El cliente ha sido registrado correctamente ✅')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al crear el cliente')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterCreate de Customer: ' . $e->getMessage(), [
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

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Customer: ' . $e->getMessage(), [
                'customer_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
