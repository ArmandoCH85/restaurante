<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Exception;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('register_stock')
                ->label('Registrar Stock')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== Purchase::STATUS_COMPLETED)
                ->action(function () {
                    try {
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
                            ->title('¡Stock registrado!')
                            ->body('El stock ha sido actualizado correctamente ✅')
                            ->success()
                            ->send();

                    } catch (QueryException $e) {
                        $friendlyMessage = $this->getFriendlyErrorMessage($e);

                        Notification::make()
                            ->title('Problema al registrar el stock')
                            ->body($friendlyMessage)
                            ->danger()
                            ->persistent()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error en register_stock action: ' . $e->getMessage(), [
                            'purchase_id' => $this->record->id,
                            'error_code' => $e->getCode()
                        ]);

                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Problema inesperado')
                            ->body('😅 Ocurrió algo inesperado. Intenta de nuevo.')
                            ->danger()
                            ->send();

                        \Illuminate\Support\Facades\Log::error('Error general en register_stock: ' . $e->getMessage(), [
                            'purchase_id' => $this->record->id
                        ]);
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Si la compra está completada, no hacer nada
        if ($this->record->status === Purchase::STATUS_COMPLETED) {
            return;
        }

        // Preguntar al usuario si desea registrar el stock
        Notification::make()
            ->title('¿Desea registrar el stock?')
            ->body('La compra ha sido guardada. Puede registrar el stock ahora o más tarde.')
            ->actions([
                \Filament\Notifications\Actions\Action::make('register')
                    ->label('Registrar ahora')
                    ->color('success')
                    ->button()
                    ->close()
                    ->action(function () {
                        try {
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
                                ->title('¡Stock registrado!')
                                ->body('El stock ha sido actualizado correctamente ✅')
                                ->success()
                                ->send();

                        } catch (QueryException $e) {
                            $friendlyMessage = $this->getFriendlyErrorMessage($e);

                            Notification::make()
                                ->title('Problema al registrar el stock')
                                ->body($friendlyMessage)
                                ->danger()
                                ->persistent()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error en afterSave register action: ' . $e->getMessage(), [
                                'purchase_id' => $this->record->id,
                                'error_code' => $e->getCode()
                            ]);

                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Problema inesperado')
                                ->body('😅 Ocurrió algo inesperado. Intenta de nuevo.')
                                ->danger()
                                ->send();

                            \Illuminate\Support\Facades\Log::error('Error general en afterSave register: ' . $e->getMessage(), [
                                'purchase_id' => $this->record->id
                            ]);
                        }
                    }),
                \Filament\Notifications\Actions\Action::make('later')
                    ->label('Más tarde')
                    ->close(),
            ])
            ->persistent()
            ->send();
    }
}
