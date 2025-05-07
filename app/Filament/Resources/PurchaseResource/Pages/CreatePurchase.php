<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
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
            ->title('Stock registrado')
            ->body('El stock ha sido actualizado automáticamente')
            ->success()
            ->send();
    }
}
