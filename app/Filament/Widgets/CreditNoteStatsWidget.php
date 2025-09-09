<?php

namespace App\Filament\Widgets;

use App\Models\CreditNote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditNoteStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    
    protected function getStats(): array
    {
        return [
            $this->getTotalCreditNotesStats(),
            $this->getTodayCreditNotesStats(),
            $this->getSuccessRateStats(),
            $this->getPendingCreditNotesStats(),
        ];
    }
    
    private function getTotalCreditNotesStats(): Stat
    {
        $total = CreditNote::count();
        $lastMonth = CreditNote::where('created_at', '>=', Carbon::now()->subMonth())->count();
        $previousMonth = CreditNote::whereBetween('created_at', [
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth()
        ])->count();
        
        $trend = $previousMonth > 0 ? (($lastMonth - $previousMonth) / $previousMonth) * 100 : 0;
        
        return Stat::make('Total Notas de Crédito', $total)
            ->description($trend >= 0 ? "+{$trend}% vs mes anterior" : "{$trend}% vs mes anterior")
            ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($trend >= 0 ? 'success' : 'danger')
            ->chart($this->getMonthlyChart());
    }
    
    private function getTodayCreditNotesStats(): Stat
    {
        $today = CreditNote::whereDate('created_at', Carbon::today())->count();
        $yesterday = CreditNote::whereDate('created_at', Carbon::yesterday())->count();
        
        $trend = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : ($today > 0 ? 100 : 0);
        
        return Stat::make('Hoy', $today)
            ->description($trend >= 0 ? "+{$trend}% vs ayer" : "{$trend}% vs ayer")
            ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($trend >= 0 ? 'success' : 'warning')
            ->chart($this->getDailyChart());
    }
    
    private function getSuccessRateStats(): Stat
    {
        $total = CreditNote::count();
        $successful = CreditNote::where('sunat_status', 'ACEPTADO')->count();
        
        $rate = $total > 0 ? ($successful / $total) * 100 : 0;
        
        return Stat::make('Tasa de Éxito SUNAT', number_format($rate, 1) . '%')
            ->description("{$successful} de {$total} aceptadas")
            ->descriptionIcon('heroicon-m-check-circle')
            ->color($rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger'))
            ->chart($this->getSuccessRateChart());
    }
    
    private function getPendingCreditNotesStats(): Stat
    {
        $pending = CreditNote::whereNull('sunat_status')
            ->orWhere('sunat_status', 'PENDIENTE')
            ->count();
            
        $rejected = CreditNote::where('sunat_status', 'RECHAZADO')->count();
        
        return Stat::make('Pendientes/Rechazadas', $pending + $rejected)
            ->description("{$pending} pendientes, {$rejected} rechazadas")
            ->descriptionIcon('heroicon-m-clock')
            ->color($pending + $rejected > 0 ? 'warning' : 'success');
    }
    
    private function getMonthlyChart(): array
    {
        return CreditNote::selectRaw('COUNT(*) as count, MONTH(created_at) as month')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')
            ->toArray();
    }
    
    private function getDailyChart(): array
    {
        return CreditNote::selectRaw('COUNT(*) as count, DATE(created_at) as date')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }
    
    private function getSuccessRateChart(): array
    {
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $total = CreditNote::whereDate('created_at', $date)->count();
            $successful = CreditNote::whereDate('created_at', $date)
                ->where('sunat_status', 'ACEPTADO')
                ->count();
                
            $rate = $total > 0 ? ($successful / $total) * 100 : 0;
            $data[] = $rate;
        }
        
        return $data;
    }
}