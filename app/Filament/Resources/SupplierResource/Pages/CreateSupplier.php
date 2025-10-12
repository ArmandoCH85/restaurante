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
     * Convierte errores técnicos de base de datos en mensajes amigables para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 ¡Ups! Ya tienes registrada una empresa con ese nombre.\n\n💡 Qué puedes hacer:\n• Cambia el nombre de la empresa\n• Busca en tu lista si ya existe este proveedor\n• Agrega algo distintivo al nombre (ej: sucursal, ciudad)";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 ¡Cuidado! Ese RUC ya está registrado en otro proveedor.\n\n💡 Qué puedes hacer:\n• Verifica que el RUC esté correcto\n• Busca el proveedor existente en tu lista\n• Si es un error, corrige el número de RUC";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 ¡Atención! Ese correo electrónico ya lo usa otro proveedor.\n\n💡 Qué puedes hacer:\n• Usa un email diferente\n• Verifica si ya tienes ese proveedor registrado\n• Pregunta al proveedor por otro email de contacto";
            }
            return "📝 Ya existe un proveedor con esos datos.\n\n💡 Revisa y cambia los valores duplicados para continuar.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 ¡Falta el nombre de la empresa!\n\n💡 Por favor, escribe el nombre completo de la empresa o negocio.";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 ¡Falta el RUC!\n\n💡 Por favor, escribe el número de RUC de la empresa (11 dígitos).";
            }
            return "📝 ¡Faltan datos importantes!\n\n💡 Completa todos los campos marcados con asterisco (*) para continuar.";
        }

        // Errores de conexión
        if (in_array($errorCode, ['2002', '2003', '2006'])) {
            return "🌐 ¡Problema de conexión a internet!\n\n💡 Qué hacer:\n• Verifica tu conexión a internet\n• Espera 10 segundos y vuelve a intentar\n• Si persiste, contacta al administrador";
        }

        // Deadlock o bloqueo de datos
        if ($errorCode == 1213) {
            return "⏳ ¡Los datos están siendo usados por otro usuario!\n\n💡 Qué hacer:\n• Cierra esta ventana\n• Espera 5 segundos\n• Abre de nuevo y vuelve a intentar";
        }

        // Error genérico
        return "😅 ¡Ups! Algo salió mal al guardar el proveedor.\n\n💡 Qué hacer:\n• Revisa que todos los datos estén correctos\n• Intenta guardar de nuevo\n• Si el problema persiste, contacta al administrador";
    }

    protected function afterCreate(): void
    {
        try {
            Notification::make()
                ->title('🎉 ¡Proveedor creado exitosamente!')
                ->body('El nuevo proveedor ha sido registrado correctamente en tu sistema.')
                ->success()
                ->duration(5000)
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('❌ No se pudo crear el proveedor')
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
                ->title('⚠️ Problema inesperado')
                ->body('😅 Ocurrió algo inesperado al crear el proveedor.\n\n💡 Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
