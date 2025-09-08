<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key) - cuando el producto está siendo usado
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'purchase_details')) {
                return "🚫 No se puede eliminar porque este producto está en compras realizadas. Primero elimina las compras relacionadas.";
            }
            if (str_contains($errorMessage, 'order_details')) {
                return "🚫 No se puede eliminar porque este producto está en pedidos realizados. Primero elimina los pedidos relacionados.";
            }
            if (str_contains($errorMessage, 'recipe_details')) {
                return "🚫 No se puede eliminar porque este producto está en recetas. Primero elimina las recetas relacionadas.";
            }
            return "🚫 No se puede eliminar porque este producto está siendo usado en otras partes del sistema.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'code')) {
                return "📄 Ya existe otro producto con ese código. Usa un código diferente.";
            }
            if (str_contains($errorMessage, 'name')) {
                return "📝 Ya existe otro producto con ese nombre. Cambia el nombre.";
            }
            return "📝 Ya existe otro producto con esos datos. Revisa y cambia los valores.";
        }

        // Errores de campo requerido (not null)
        if ($errorCode == 23000 && str_contains($errorMessage, 'cannot be null')) {
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
                    // Verificar si el producto puede ser eliminado
                    $product = $this->record;

                    // Verificar si está en compras
                    if ($product->purchaseDetails()->exists()) {
                        throw new \Exception("Este producto no puede ser eliminado porque está registrado en compras.");
                    }

                    // Verificar si está en pedidos
                    if ($product->orderDetails()->exists()) {
                        throw new \Exception("Este producto no puede ser eliminado porque está registrado en pedidos.");
                    }

                    // Verificar si está en recetas
                    if ($product->recipeDetails()->exists()) {
                        throw new \Exception("Este producto no puede ser eliminado porque está siendo usado en recetas.");
                    }
                })
                ->action(function () {
                    try {
                        $product = $this->record;
                        $productName = $product->name;

                        $product->delete();

                        Notification::make()
                            ->title('¡Producto eliminado!')
                            ->body("El producto '{$productName}' ha sido eliminado correctamente ✅")
                            ->success()
                            ->send();

                        return redirect()->route('filament.admin.resources.products.index');

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('No se puede eliminar el producto')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error al eliminar producto: ' . $e->getMessage(), [
                            'product_id' => $this->record->id,
                            'product_name' => $this->record->name,
                            'error_code' => $e->getCode(),
                            'error_message' => $e->getMessage()
                        ]);

                    } catch (Exception $e) {
                        $friendlyMessage = "🚫 " . $e->getMessage();

                        Notification::make()
                            ->title('No se puede eliminar el producto')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general al eliminar producto: ' . $e->getMessage(), [
                            'product_id' => $this->record->id,
                            'product_name' => $this->record->name
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        try {
            Notification::make()
                ->title('¡Producto actualizado!')
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

            \Illuminate\Support\Facades\Log::error('Error en afterSave de Product: ' . $e->getMessage(), [
                'product_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            \Illuminate\Support\Facades\Log::error('Error general en afterSave de Product: ' . $e->getMessage(), [
                'product_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
