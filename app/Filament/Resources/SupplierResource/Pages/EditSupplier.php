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
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key) - cuando el proveedor está siendo usado
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'purchases')) {
                return "🚫 No se puede eliminar porque este proveedor tiene compras registradas. Primero elimina las compras relacionadas.";
            }
            if (str_contains($errorMessage, 'products')) {
                return "🚫 No se puede eliminar porque este proveedor está asignado a productos. Primero cambia el proveedor de esos productos.";
            }
            return "🚫 No se puede eliminar porque este proveedor está siendo usado en otras partes del sistema.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'business_name')) {
                return "🏢 Ya existe otro proveedor con esa razón social. Usa un nombre diferente.";
            }
            if (str_contains($errorMessage, 'tax_id')) {
                return "📄 Ya existe otro proveedor con ese RUC. Verifica el RUC.";
            }
            if (str_contains($errorMessage, 'email')) {
                return "📧 Ya existe otro proveedor con ese correo electrónico. Usa otro email.";
            }
            return "📝 Ya existe otro proveedor con esos datos. Revisa y cambia los valores.";
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
        return "😅 Ocurrió un problema al guardar los cambios. Revisa los datos e intenta de nuevo.";
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
                        throw new \Exception("Este proveedor no puede ser eliminado porque tiene compras registradas.");
                    }

                    // Verificar si está asignado a productos
                    if ($supplier->products()->exists()) {
                        throw new \Exception("Este proveedor no puede ser eliminado porque está asignado a productos.");
                    }
                })
                ->action(function () {
                    try {
                        $supplier = $this->record;
                        $supplierName = $supplier->business_name;

                        $supplier->delete();

                        Notification::make()
                            ->title('¡Proveedor eliminado!')
                            ->body("El proveedor '{$supplierName}' ha sido eliminado correctamente ✅")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.suppliers.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('No se puede eliminar el proveedor')
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
                        $friendlyMessage = "🚫 " . $e->getMessage();

                        Notification::make()
                            ->title('No se puede eliminar el proveedor')
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
                            ->title('¡Proveedor eliminado permanentemente!')
                            ->body("El proveedor '{$supplierName}' ha sido eliminado permanentemente 🗑️")
                            ->warning()
                            ->send();

                        return redirect()->route('filament.admin.resources.suppliers.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('No se puede eliminar permanentemente')
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
                            ->title('Problema inesperado')
                            ->body('😅 Ocurrió algo inesperado al eliminar permanentemente.')
                            ->danger()
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
                            ->title('¡Proveedor restaurado!')
                            ->body("El proveedor '{$supplierName}' ha sido restaurado correctamente 🔄")
                            ->success()
                            ->send();

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('Problema al restaurar')
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
                            ->title('Problema inesperado')
                            ->body('😅 Ocurrió algo inesperado al restaurar.')
                            ->danger()
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
                ->title('¡Proveedor actualizado!')
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

            \Illuminate\Support\Facades\Log::error('Error en afterSave de Supplier: ' . $e->getMessage(), [
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

            \Illuminate\Support\Facades\Log::error('Error general en afterSave de Supplier: ' . $e->getMessage(), [
                'supplier_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
