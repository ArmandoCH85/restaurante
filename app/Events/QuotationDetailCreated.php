<?php

namespace App\Events;

use App\Models\QuotationDetail;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationDetailCreated
{
    use Dispatchable, SerializesModels;

    /**
     * El detalle de cotización que se creó.
     *
     * @var \App\Models\QuotationDetail
     */
    public $quotationDetail;

    /**
     * Crea una nueva instancia del evento.
     *
     * @param  \App\Models\QuotationDetail  $quotationDetail
     * @return void
     */
    public function __construct(QuotationDetail $quotationDetail)
    {
        $this->quotationDetail = $quotationDetail;
    }
}
