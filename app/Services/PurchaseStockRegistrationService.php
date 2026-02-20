<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;

class PurchaseStockRegistrationService
{
    /**
     * Registra movimientos de stock para los detalles de una compra.
     * Es idempotente por detalle (usa marcador "Detalle #ID" en notas).
     *
     * @return array{
     *     success: bool,
     *     purchase_id: int,
     *     processed_details: int,
     *     skipped_details: int,
     *     errors: array<int, array<string, mixed>>
     * }
     */
    public function register(Purchase $purchase, ?int $createdBy = null): array
    {
        $purchase->loadMissing('details');

        $processedDetails = 0;
        $skippedDetails = 0;
        $errors = [];

        foreach ($purchase->details as $detail) {
            if ($this->detailAlreadyRegistered($purchase, $detail->id, $detail->product_id)) {
                $skippedDetails++;

                continue;
            }

            $product = Product::find($detail->product_id);

            if (! $product) {
                $errors[] = [
                    'detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'message' => 'Producto no encontrado',
                ];

                continue;
            }

            try {
                InventoryMovement::createPurchaseMovement(
                    productId: $detail->product_id,
                    warehouseId: $purchase->warehouse_id,
                    quantity: (float) $detail->quantity,
                    unitCost: (float) $detail->unit_cost,
                    purchaseId: $purchase->id,
                    referenceDocument: $purchase->document_number,
                    createdBy: $createdBy ?? $purchase->created_by,
                    notes: sprintf(
                        'Compra: %s %s | Detalle #%d',
                        $purchase->document_type,
                        $purchase->document_number,
                        $detail->id
                    )
                );

                $processedDetails++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'message' => $e->getMessage(),
                ];

                Log::error('Error registrando stock de compra por detalle', [
                    'purchase_id' => $purchase->id,
                    'detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => empty($errors),
            'purchase_id' => $purchase->id,
            'processed_details' => $processedDetails,
            'skipped_details' => $skippedDetails,
            'errors' => $errors,
        ];
    }

    private function detailAlreadyRegistered(Purchase $purchase, int $detailId, int $productId): bool
    {
        return InventoryMovement::query()
            ->where('reference_type', Purchase::class)
            ->where('reference_id', $purchase->id)
            ->where('movement_type', InventoryMovement::TYPE_PURCHASE)
            ->where('product_id', $productId)
            ->where('notes', 'LIKE', '%Detalle #'.$detailId.'%')
            ->exists();
    }
}
