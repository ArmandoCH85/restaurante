<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 Ya existe un proveedor con esa razón social. Usa un nombre diferente.";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 Ya existe un proveedor con ese RUC. Verifica el RUC.";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 Ya existe un proveedor con ese correo electrónico. Usa otro email.";
            }
            return "📝 Ya existe un proveedor con esos datos. Revisa y cambia los valores.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 La razón social es obligatoria. Completa este campo.";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 El RUC es obligatorio. Completa este campo.";
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
        return "😅 Ocurrió un problema al guardar el proveedor. Revisa los datos e intenta de nuevo.";
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('¡Proveedor creado!')
                ->body('El proveedor ha sido registrado correctamente ✅')
                ->success()
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al crear el proveedor')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterCreate de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
