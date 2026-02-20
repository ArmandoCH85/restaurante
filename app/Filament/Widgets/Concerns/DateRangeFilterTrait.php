<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;

/**
 * Centraliza la resolución de rangos de fecha para widgets Filament.
 */
trait DateRangeFilterTrait
{
    private const MAX_CUSTOM_RANGE_DAYS = 31;

    /**
     * Devuelve array [$start, $end] (Carbon instances) según filtros estándar.
     */
    protected function resolveDateRange(array $filters): array
    {
        $range = $filters['date_range'] ?? 'today';
        $start = $filters['start_date'] ?? null;
        $end = $filters['end_date'] ?? null;

        return match ($range) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'last_7_days' => [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()],
            'last_30_days' => [Carbon::today()->subDays(29)->startOfDay(), Carbon::today()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()],
            'custom' => $this->resolveCustomRange($start, $end),
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    private function resolveCustomRange(mixed $start, mixed $end): array
    {
        if (! $start || ! $end) {
            return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
        }

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        if ($startDate->diffInDays($endDate) >= self::MAX_CUSTOM_RANGE_DAYS) {
            $endDate = $startDate->copy()->addDays(self::MAX_CUSTOM_RANGE_DAYS - 1)->endOfDay();
        }

        return [$startDate, $endDate];
    }

    /**
     * Etiqueta humana del rango.
     */
    protected function humanRangeLabel(Carbon $start, Carbon $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->isToday() ? 'Hoy' : ($start->isYesterday() ? 'Ayer' : $start->format('d/m/Y'));
        }
        return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
    }
}
