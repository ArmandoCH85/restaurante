<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

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
                        ->body('El stock ha sido actualizado correctamente')
                        ->success()
                        ->send();
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
                            ->body('El stock ha sido actualizado correctamente')
                            ->success()
                            ->send();
                    }),
                \Filament\Notifications\Actions\Action::make('later')
                    ->label('Más tarde')
                    ->close(),
            ])
            ->persistent()
            ->send();
    }
}
