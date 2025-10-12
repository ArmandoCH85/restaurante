<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Convierte errores técnicos de base de datos en mensajes simples para usuarios
     */
    private function getFriendlyErrorMessage(QueryException $exception): string
    {
        $errorCode = $exception->getCode();
        $errorMessage = $exception->getMessage();

        // Errores de clave foránea (foreign key)
        if ($errorCode == 23000 && str_contains($errorMessage, 'foreign key constraint')) {
            if (str_contains($errorMessage, 'supplier_id')) {
                return "🚫 El proveedor seleccionado no existe. Verifica que esté registrado correctamente.";
            }
            if (str_contains($errorMessage, 'product_id')) {
                return "🚫 Uno de los productos seleccionados no existe. Revisa la lista de productos.";
            }
            if (str_contains($errorMessage, 'warehouse_id')) {
                return "🚫 El almacén seleccionado no existe. Elige otro almacén.";
            }
            return "🚫 No se puede guardar porque hay datos relacionados que no existen.";
        }

        // Errores de duplicado (unique constraint)
        if ($errorCode == 23000 && str_contains($errorMessage, 'Duplicate entry')) {
            if (str_contains($errorMessage, 'document_number')) {
                return "📄 Ya existe una compra con ese número de documento. Cambia el número.";
            }
            return "📝 Ya existe un registro con esos datos. Revisa y cambia los valores duplicados.";
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
            return "⏳ Los datos están ocupados por otro proceso. Cierra esta ventana, espera 5 segundos y abre de nuevo.";
        }

        // Error genérico
        return "😅 Ocurrió un problema al guardar. Revisa los datos e intenta de nuevo.";
    }

    protected function afterCreate(): void
    {
        try {
            // Registrar automáticamente el stock después de crear la compra
            $purchase = $this->record;

            // Recorrer todos los detalles de la compra
            foreach ($purchase->details as $detail) {
                // Buscar el producto (puede ser ingrediente u otro tipo de producto)
                $product = \App\Models\Product::find($detail->product_id);

                if ($product) {
                    // Crear movimiento de inventario
                    InventoryMovement::createPurchaseMovement(
                        $product->id,
                        $detail->quantity,
                        $detail->unit_cost,
                        $purchase->id,
                        $purchase->document_number,
                        $purchase->created_by,
                        "Compra: {$purchase->document_type} {$purchase->document_number}"
                    );
                }
            }

            // Actualizar el estado de la compra a completado
            $purchase->status = Purchase::STATUS_COMPLETED;
            $purchase->save();

            Notification::make()
                ->title('¡Compra registrada!')
                ->body('La compra y el stock han sido registrados correctamente ✅')
                ->success()
                ->send();

        } catch (QueryException $e) {
            // Error de base de datos - mostrar mensaje amigable
            $friendlyMessage = $this->getFriendlyErrorMessage($e);

            Notification::make()
                ->title('Problema al registrar el stock')
                ->body($friendlyMessage)
                ->danger()
                ->persistent()
                ->send();

            // Log del error técnico para debugging
            \Illuminate\Support\Facades\Log::error('Error en afterCreate de Purchase: ' . $e->getMessage(), [
                'purchase_id' => $this->record->id ?? null,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage()
            ]);

        } catch (Exception $e) {
            // Error general - mostrar mensaje amigable
            Notification::make()
                ->title('Problema inesperado')
                ->body('😅 Ocurrió algo inesperado. Cierra esta ventana y abre de nuevo para continuar.')
                ->danger()
                ->send();

            // Log del error técnico
            \Illuminate\Support\Facades\Log::error('Error general en afterCreate de Purchase: ' . $e->getMessage(), [
                'purchase_id' => $this->record->id ?? null,
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
