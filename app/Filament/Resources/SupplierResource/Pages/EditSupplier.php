<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes amigables para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key) - cuando el proveedor está siendo usado
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'purchases')) {
                $purchasesCount = $this->record->purchases()->count();
                return "🚫 ¡No se puede eliminar este proveedor!\n\n📋 Motivo: Tiene {$purchasesCount} compra(s) registrada(s).\n\n💡 Qué hacer:\n• Elimina primero todas las compras de este proveedor\n• O transfiere las compras a otro proveedor\n• Luego podrás eliminar este proveedor";
            }
            if (str_contains($errorMessage, 'ingredients')) {
                $ingredientsCount = $this->record->ingredients()->count();
                $ingredientsList = $this->record->ingredients()->limit(3)->pluck('name')->join(', ');
                $moreText = $ingredientsCount > 3 ? " y " . ($ingredientsCount - 3) . " más" : "";
                
                return "🚫 ¡No se puede eliminar este proveedor!\n\n🥬 Motivo: Está asignado a {$ingredientsCount} ingrediente(s): {$ingredientsList}{$moreText}.\n\n💡 Qué hacer:\n• Cambia el proveedor de esos ingredientes\n• O elimina los ingredientes que ya no uses\n• Luego podrás eliminar este proveedor";
            }
            return "🚫 ¡No se puede eliminar este proveedor!\n\n📋 Motivo: Está siendo usado en otras partes del sistema.\n\n💡 Revisa las compras e ingredientes asociados antes de eliminar.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 ¡Ups! Ya tienes otro proveedor con ese nombre de empresa.\n\n💡 Qué puedes hacer:\n• Cambia el nombre de la empresa\n• Busca en tu lista si ya existe este proveedor\n• Agrega algo distintivo al nombre (ej: sucursal, ciudad)";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 ¡Cuidado! Ese RUC ya está registrado en otro proveedor.\n\n💡 Qué puedes hacer:\n• Verifica que el RUC esté correcto\n• Busca el proveedor existente en tu lista\n• Si es un error, corrige el número de RUC";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 ¡Atención! Ese correo electrónico ya lo usa otro proveedor.\n\n💡 Qué puedes hacer:\n• Usa un email diferente\n• Verifica si ya tienes ese proveedor registrado\n• Pregunta al proveedor por otro email de contacto";
            }
            return "📝 Ya existe otro proveedor con esos datos.\n\n💡 Revisa y cambia los valores duplicados para continuar.";
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
        return "😅 ¡Ups! Algo salió mal al guardar los cambios.\n\n💡 Qué hacer:\n• Revisa que todos los datos estén correctos\n• Intenta guardar de nuevo\n• Si el problema persiste, contacta al administrador";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    // Verificar si el proveedor puede ser eliminado
                    $supplier = $this->record;

                    // Verificar si tiene compras
                    if ($supplier->purchases()->exists()) {
                        $purchasesCount = $supplier->purchases()->count();
                        throw new \Exception("🚫 ¡No se puede eliminar este proveedor!\n\n📋 Motivo: Tiene {$purchasesCount} compra(s) registrada(s).\n\n💡 Qué hacer:\n• Elimina primero todas las compras de este proveedor\n• O transfiere las compras a otro proveedor\n• Luego podrás eliminar este proveedor");
                    }

                    // Verificar si está asignado a ingredientes
                    if ($supplier->ingredients()->exists()) {
                        $ingredientsCount = $supplier->ingredients()->count();
                        $ingredientsList = $supplier->ingredients()->limit(3)->pluck('name')->join(', ');
                        $moreText = $ingredientsCount > 3 ? " y " . ($ingredientsCount - 3) . " más" : "";
                        
                        throw new \Exception("🚫 ¡No se puede eliminar este proveedor!\n\n🥬 Motivo: Está asignado a {$ingredientsCount} ingrediente(s): {$ingredientsList}{$moreText}.\n\n💡 Qué hacer:\n• Cambia el proveedor de esos ingredientes\n• O elimina los ingredientes que ya no uses\n• Luego podrás eliminar este proveedor");
                    }
                })
                ->action(function () {
                    try {
                        $supplier = $this->record;
                        $supplierName = $supplier->business_name;

                        $supplier->delete();

                        Notification::make()
                            ->title('🎉 ¡Proveedor eliminado exitosamente!')
                            ->body("El proveedor '{$supplierName}' ha sido eliminado correctamente.")
                            ->success()
                            ->duration(5000)
                            ->send();

                        return redirect()->route('filament.admin.resources.suppliers.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('❌ No se pudo eliminar el proveedor')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al eliminar proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id,
                            'supplier_name' => $this->record->business_name,
                            'error_code' => $e->getCode(),
                            'error_message' => $e->getMessage()
                        ]);

                    } catch (Exception $e) {
                        $friendlyMessage = $e->getMessage();

                        Notification::make()
                            ->title('❌ No se pudo eliminar el proveedor')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al eliminar proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id,
                            'supplier_name' => $this->record->business_name
                        ]);
                    }
                }),

            Actions\ForceDeleteAction::make()
                ->action(function () {
                    try {
                        $supplier = $this->record;
                        $supplierName = $supplier->business_name;

                        $supplier->forceDelete();

                        Notification::make()
                            ->title('🗑️ ¡Proveedor eliminado permanentemente!')
                            ->body("El proveedor '{$supplierName}' ha sido eliminado permanentemente del sistema.")
                            ->warning()
                            ->duration(5000)
                            ->send();

                        return redirect()->route('filament.admin.resources.suppliers.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('❌ No se pudo eliminar permanentemente')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al eliminar permanentemente proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id,
                            'supplier_name' => $this->record->business_name,
                            'error_code' => $e->getCode()
                        ]);

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('⚠️ Problema inesperado')
                            ->body('😅 Ocurrió algo inesperado al eliminar permanentemente el proveedor.\n\n💡 Contacta al administrador si el problema persiste.')
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al eliminar permanentemente proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id
                        ]);
                    }
                }),

            Actions\RestoreAction::make()
                ->action(function () {
                    try {
                        $supplier = $this->record;
                        $supplierName = $supplier->business_name;

                        $supplier->restore();

                        Notification::make()
                            ->title('🔄 ¡Proveedor restaurado exitosamente!')
                            ->body("El proveedor '{$supplierName}' ha sido restaurado correctamente.")
                            ->success()
                            ->duration(5000)
                            ->send();

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('❌ No se pudo restaurar el proveedor')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al restaurar proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id,
                            'supplier_name' => $this->record->business_name,
                            'error_code' => $e->getCode()
                        ]);

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('⚠️ Problema inesperado')
                            ->body('😅 Ocurrió algo inesperado al restaurar el proveedor.\n\n💡 Contacta al administrador si el problema persiste.')
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al restaurar proveedor: ' . $e->getMessage(), [
                            'supplier_id' => $this->record->id
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            Notification::make()
                ->title('🎉 ¡Proveedor actualizado exitosamente!')
                ->body('Los cambios han sido guardados correctamente en el sistema.')
                ->success()
                ->duration(5000)
                ->send();

        } catch (QueryException $e) {
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('❌ No se pudieron guardar los cambios')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error en afterSave de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('⚠️ Problema inesperado')
                ->body('😅 Ocurrió algo inesperado al guardar los cambios.\n\n💡 Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->persistent()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterSave de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
