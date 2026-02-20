<?php

namespace App\Services;

use App\Models\InvoiceDetail;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductsByChannelProfitService
{
    public function getByChannel(
        ?CarbonInterface $startDateTime = null,
        ?CarbonInterface $endDateTime = null,
        ?string $channelFilter = null,
        ?string $invoiceType = null
    ): Collection {
        $query = InvoiceDetail::query()
            ->from('invoice_details as d')
            ->join('invoices as i', 'd.invoice_id', '=', 'i.id')
            ->join('orders as o', 'i.order_id', '=', 'o.id')
            ->join('products as p', 'd.product_id', '=', 'p.id')
            ->whereNotNull('i.order_id')
            ->where('o.billed', true);

        if ($startDateTime && $endDateTime) {
            $query->whereBetween('o.order_datetime', [$startDateTime, $endDateTime]);
        }

        if ($channelFilter) {
            $query->where('o.service_type', $channelFilter);
        }

        $this->applyValidInvoiceFilters($query);
        $this->applyInvoiceTypeFilter($query, $invoiceType);

        return $query
            ->selectRaw('o.service_type')
            ->selectRaw('SUM(d.quantity) as total_quantity')
            ->selectRaw('SUM(d.subtotal) as total_sales')
            ->selectRaw('SUM(d.quantity * COALESCE(p.current_cost, 0)) as total_cost')
            ->selectRaw('SUM(d.subtotal - (d.quantity * COALESCE(p.current_cost, 0))) as total_profit')
            ->groupBy('o.service_type')
            ->orderBy('o.service_type')
            ->get();
    }

    private function applyValidInvoiceFilters(Builder $query): void
    {
        $query->whereNull('i.voided_date')
            ->where(function (Builder $taxQuery): void {
                $taxQuery->whereNull('i.tax_authority_status')
                    ->orWhereRaw('LOWER(i.tax_authority_status) NOT IN (?, ?)', ['voided', 'rejected']);
            })
            ->where(function (Builder $sunatQuery): void {
                $sunatQuery->whereNull('i.sunat_status')
                    ->orWhereRaw('UPPER(i.sunat_status) <> ?', ['RECHAZADO']);
            });
    }

    private function applyInvoiceTypeFilter(Builder $query, ?string $invoiceType): void
    {
        if (! $invoiceType) {
            return;
        }

        switch ($invoiceType) {
            case 'sales_note':
                $query->where('i.series', 'LIKE', 'NV%');
                break;
            case 'receipt':
                $query->where('i.series', 'LIKE', 'B%');
                break;
            case 'invoice':
                $query->where('i.series', 'LIKE', 'F%');
                break;
            default:
                $query->where('i.invoice_type', $invoiceType);
                break;
        }
    }
}
