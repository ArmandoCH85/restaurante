<?php

namespace App\Services;

use App\Models\InvoiceDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SalesByAreaReportService
{
    public function aggregateQuery(string $from, string $to, ?int $areaId, string $groupBy): Builder
    {
        $periodSql = $this->getPeriodSql($groupBy);
        $groupByColumns = array_merge(
            $this->getPeriodGroupByColumns($groupBy),
            ['a.id', 'a.name']
        );

        return InvoiceDetail::query()
            ->join('invoices as i', 'invoice_details.invoice_id', '=', 'i.id')
            ->join('products as p', 'p.id', '=', 'invoice_details.product_id')
            ->join('areas as a', 'a.id', '=', 'p.area_id')
            ->whereBetween('i.issue_date', [$from, $to])
            ->whereNull('p.deleted_at')
            ->whereNotIn('i.tax_authority_status', ['voided', 'rejected'])
            ->where(function ($query): void {
                $query->whereNull('i.sunat_status')
                    ->orWhere('i.sunat_status', '!=', 'RECHAZADO');
            })
            ->when($areaId, fn (Builder $query): Builder => $query->where('p.area_id', $areaId))
            ->selectRaw('MIN(invoice_details.id) as id')
            ->selectRaw('a.id as area_id')
            ->selectRaw('a.name as area_name')
            ->selectRaw("{$periodSql} as period_key")
            ->selectRaw("{$periodSql} as period_label")
            ->selectRaw('SUM(invoice_details.quantity) as units_sold')
            ->selectRaw('SUM(invoice_details.subtotal) as net_sold')
            ->groupBy(...$groupByColumns)
            ->orderBy('period_key')
            ->orderBy('area_name');
    }

    public function drillDownQuery(string $from, string $to, int $areaId, string $groupBy, string $period): Builder
    {
        $query = InvoiceDetail::query()
            ->from('invoice_details as d')
            ->join('invoices as i', 'd.invoice_id', '=', 'i.id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->join('areas as a', 'a.id', '=', 'p.area_id')
            ->whereBetween('i.issue_date', [$from, $to])
            ->where('p.area_id', $areaId)
            ->whereNull('p.deleted_at')
            ->whereNotIn('i.tax_authority_status', ['voided', 'rejected'])
            ->where(function ($subQuery): void {
                $subQuery->whereNull('i.sunat_status')
                    ->orWhere('i.sunat_status', '!=', 'RECHAZADO');
            });

        if ($groupBy === 'month') {
            $query->whereRaw($this->getMonthFilterSql().' = ?', [$period]);
        } else {
            $query->whereDate('i.issue_date', $period);
        }

        return $query
            ->selectRaw('p.id as product_id')
            ->selectRaw('p.code as product_code')
            ->selectRaw('p.name as product_name')
            ->selectRaw('SUM(d.quantity) as units_sold')
            ->selectRaw('SUM(d.subtotal) as net_sold')
            ->groupBy('p.id', 'p.code', 'p.name')
            ->orderBy('product_name');
    }

    private function getPeriodSql(string $groupBy): string
    {
        if ($groupBy !== 'month') {
            return 'DATE(i.issue_date)';
        }

        return $this->usesSqlite()
            ? "strftime('%Y-%m', i.issue_date)"
            : "CONCAT(LPAD(YEAR(i.issue_date), 4, '0'), '-', LPAD(MONTH(i.issue_date), 2, '0'))";
    }

    /**
     * @return array<int, \Illuminate\Contracts\Database\Query\Expression|string>
     */
    private function getPeriodGroupByColumns(string $groupBy): array
    {
        if ($groupBy !== 'month') {
            return [DB::raw('DATE(i.issue_date)')];
        }

        if ($this->usesSqlite()) {
            return [DB::raw("strftime('%Y-%m', i.issue_date)")];
        }

        return [
            DB::raw('YEAR(i.issue_date)'),
            DB::raw('MONTH(i.issue_date)'),
        ];
    }

    private function getMonthFilterSql(): string
    {
        return $this->usesSqlite()
            ? "strftime('%Y-%m', i.issue_date)"
            : "DATE_FORMAT(i.issue_date, '%Y-%m')";
    }

    private function usesSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
