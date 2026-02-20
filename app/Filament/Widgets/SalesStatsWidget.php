<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\CashRegister;
use Carbon\Carbon;
use App\Filament\Widgets\Concerns\DateRangeFilterTrait;

class SalesStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use DateRangeFilterTrait;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
        '2xl' => 'full',
    ];

    protected static bool $isLazy = false;

    protected $listeners = [
        'filtersFormUpdated' => '$refresh',
        'updateCharts' => '$refresh',
    ];

    public function updatedFilters(): void
    {
        $this->dispatch('updateCharts');
    }

    protected function getStats(): array
    {
        [$start, $end] = $this->resolveDateRange($this->filters ?? []);

        return [
            $this->getTotalSalesStat($start, $end),
            $this->getOperationsCountStat($start, $end),
            $this->getMesaSalesStat($start, $end),
        ];
    }

    private function getOperationsCountStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $count = 0;
        
        // Para el día actual: contar órdenes de cajas abiertas o sin caja
        if ($endDate->isToday() || $endDate->isFuture()) {
            $todayCount = Order::whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->where('billed', true)
                ->where(function($q) {
                    $q->whereHas('cashRegister', function ($subQ) {
                        $subQ->where('is_active', CashRegister::STATUS_OPEN);
                    })
                    ->orWhereNull('cash_register_id');
                })
                ->count();
            
            $count += $todayCount;
            
            // Si el rango incluye días pasados, también contarlos
            if (!$startDate->isToday()) {
                $pastEndDate = today()->subDay();
                if ($startDate->lte($pastEndDate)) {
                    $pastCount = Order::whereBetween('created_at', [$startDate, $pastEndDate->endOfDay()])
                        ->where('status', '!=', 'cancelled')
                        ->where('billed', true)
                        ->where(function($q) {
                            $q->whereHas('cashRegister', function ($subQ) {
                                $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                            })
                            ->orWhereNull('cash_register_id');
                        })
                        ->count();
                    
                    $count += $pastCount;
                }
            }
        } else {
            // Solo fechas pasadas: contar órdenes de cajas cerradas
            $count = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->where('billed', true)
                ->where(function($q) {
                    $q->whereHas('cashRegister', function ($subQ) {
                        $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                    })
                    ->orWhereNull('cash_register_id');
                })
                ->count();
        }

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Órdenes Facturadas', number_format($count))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-calculator')
            ->color('primary');
    }

    private function getTotalSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = 0;
        
        // Para el día actual: sumar órdenes de cajas abiertas o sin caja
        if ($endDate->isToday() || $endDate->isFuture()) {
            $todayTotal = Order::whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->where('billed', true)
                ->where(function($q) {
                    $q->whereHas('cashRegister', function ($subQ) {
                        $subQ->where('is_active', CashRegister::STATUS_OPEN);
                    })
                    ->orWhereNull('cash_register_id');
                })
                ->sum('total');
            
            $total += $todayTotal;
            
            // Si el rango incluye días pasados, usar total_sales de cajas cerradas
            if (!$startDate->isToday()) {
                $pastEndDate = today()->subDay();
                if ($startDate->lte($pastEndDate)) {
                    $pastTotal = CashRegister::whereBetween('closing_datetime', [$startDate, $pastEndDate->endOfDay()])
                        ->where('is_active', CashRegister::STATUS_CLOSED)
                        ->sum('total_sales');
                    
                    $total += $pastTotal;
                }
            }
        } else {
            // Solo fechas pasadas: usar total_sales de cajas cerradas
            $total = CashRegister::whereBetween('closing_datetime', [$startDate, $endDate])
                ->where('is_active', CashRegister::STATUS_CLOSED)
                ->sum('total_sales');
        }

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Ventas Facturadas', 'S/ ' . number_format($total, 2))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success');
    }

    private function getMesaSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = 0;
        
        // Para el día actual: sumar órdenes de mesa de cajas abiertas o sin caja
        if ($endDate->isToday() || $endDate->isFuture()) {
            $todayTotal = Order::whereDate('created_at', today())
                ->where('service_type', 'dine_in')
                ->where('status', '!=', 'cancelled')
                ->where('billed', true)
                ->where(function($q) {
                    $q->whereHas('cashRegister', function ($subQ) {
                        $subQ->where('is_active', CashRegister::STATUS_OPEN);
                    })
                    ->orWhereNull('cash_register_id');
                })
                ->sum('total');
            
            $total += $todayTotal;
            
            // Si el rango incluye días pasados, usar datos de cajas cerradas
            if (!$startDate->isToday()) {
                $pastEndDate = today()->subDay();
                if ($startDate->lte($pastEndDate)) {
                    // Para fechas pasadas, sumar órdenes de mesa de cajas cerradas
                    $pastTotal = Order::whereBetween('created_at', [$startDate, $pastEndDate->endOfDay()])
                        ->where('service_type', 'dine_in')
                        ->where('status', '!=', 'cancelled')
                        ->where('billed', true)
                        ->where(function($q) {
                            $q->whereHas('cashRegister', function ($subQ) {
                                $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                            })
                            ->orWhereNull('cash_register_id');
                        })
                        ->sum('total');
                    
                    $total += $pastTotal;
                }
            }
        } else {
            // Solo fechas pasadas: sumar órdenes de mesa de cajas cerradas
            $total = Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('service_type', 'dine_in')
                ->where('status', '!=', 'cancelled')
                ->where('billed', true)
                ->where(function($q) {
                    $q->whereHas('cashRegister', function ($subQ) {
                        $subQ->where('is_active', CashRegister::STATUS_CLOSED);
                    })
                    ->orWhereNull('cash_register_id');
                })
                ->sum('total');
        }

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Ventas en Mesa', 'S/ ' . number_format($total, 2))
            ->description("Período {$dateRange}")
            ->descriptionIcon('heroicon-m-home')
            ->color('emerald');
    }

    // Wrapper para compatibilidad con código previo
    private function getDateRangeDescription(Carbon $startDate, Carbon $endDate): string
    {
        return $this->humanRangeLabel($startDate, $endDate);
    }

    /**
     * 📈 Gráfico simple de ventas de los últimos 7 días
     */
    // Eliminado gráfico embebido ficticio para evitar datos sinteticos
    private function getSalesChart(): array { return []; }
}
