<?php

namespace App\Listeners;

use App\Events\QuotationDetailCreated;
use App\Events\QuotationDetailUpdated;
use App\Events\QuotationDetailDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecalculateQuotationTotals implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Maneja el evento de creación de detalle de cotización.
     *
     * @param  \App\Events\QuotationDetailCreated  $event
     * @return void
     */
    public function handleCreated(QuotationDetailCreated $event)
    {
        $this->recalculateTotals($event->quotationDetail);
    }

    /**
     * Maneja el evento de actualización de detalle de cotización.
     *
     * @param  \App\Events\QuotationDetailUpdated  $event
     * @return void
     */
    public function handleUpdated(QuotationDetailUpdated $event)
    {
        $this->recalculateTotals($event->quotationDetail);
    }

    /**
     * Maneja el evento de eliminación de detalle de cotización.
     *
     * @param  \App\Events\QuotationDetailDeleted  $event
     * @return void
     */
    public function handleDeleted(QuotationDetailDeleted $event)
    {
        $this->recalculateTotals($event->quotationDetail);
    }

    /**
     * Recalcula los totales de la cotización.
     *
     * @param  \App\Models\QuotationDetail  $detail
     * @return void
     */
    private function recalculateTotals($detail)
    {
        try {
            // Obtener la cotización
            $quotation = $detail->quotation;
            
            if ($quotation) {
                // Recalcular los totales
                $quotation->recalculateTotals();
                
                Log::info('Totales de cotización recalculados por evento de detalle', [
                    'quotation_id' => $quotation->id,
                    'detail_id' => $detail->id,
                    'subtotal' => $quotation->subtotal,
                    'tax' => $quotation->tax,
                    'total' => $quotation->total
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al recalcular totales de cotización', [
                'detail_id' => $detail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
