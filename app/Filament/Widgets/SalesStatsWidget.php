<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Pages\Dashboard\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Carbon\Carbon;
use App\Models\Invoice;

class SalesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // 📐 GRID RESPONSIVO - 3 ESTADÍSTICAS PRINCIPALES
    protected int | string | array $columnSpan = [
        'default' => 'full',  // Móvil: ancho completo
        'sm' => 'full',       // Tablet pequeña: ancho completo
        'md' => 'full',       // Tablet: ancho completo (3 estadísticas en línea)
        'lg' => 'full',       // Desktop: ancho completo
        'xl' => 'full',       // Desktop grande: ancho completo
        '2xl' => 'full',      // Desktop extra: ancho completo
    ];

    protected function getStats(): array
    {
        // 📅 OBTENER RANGO DE FECHAS DESDE LOS FILTROS
        $dateRange = $this->filters['date_range'] ?? 'today';
        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;

        // 🎯 CALCULAR FECHAS SEGÚN EL RANGO SELECCIONADO
        $dates = $this->getDateRange($dateRange, $startDate, $endDate);

        return [
            // 📊 LAS 3 MÉTRICAS MÁS IMPORTANTES CON FILTRO DE FECHA
            $this->getTotalSalesStat($dates['start'], $dates['end']),          // 💰 Total Ventas
            $this->getOperationsCountStat($dates['start'], $dates['end']),      // 🔢 N° Operaciones
            $this->getMesaSalesStat($dates['start'], $dates['end']),           // 🍽️ Ventas Mesa
        ];
    }

    // 🔢 N° OPERACIONES
    private function getOperationsCountStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $count = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->count();

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('N° Operaciones', number_format($count))
            ->description("Órdenes procesadas {$dateRange}")
            ->descriptionIcon('heroicon-m-calculator')
            ->color('primary')
            ->chart([7, 12, 8, 15, 10, 18, $count])
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200'
            ]);
    }

    // 💰 TOTAL VENTAS
    private function getTotalSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Total Ventas', 'S/ ' . number_format($total, 2))
            ->description("Ingresos {$dateRange}")
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success')
            ->chart([120, 180, 150, 200, 170, 250, $total])
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-green-50 to-green-100 border-green-200'
            ]);
    }

    // 📝 TOTAL NOTAS DE VENTA
    private function getSalesNotesStat(Carbon $date): Stat
    {
        $total = Invoice::whereDate('created_at', $date)
            ->where('invoice_type', 'receipt')
            ->whereNull('order_id') // Notas de venta no tienen order_id
            ->where('tax_authority_status', '!=', 'voided')
            ->sum('total');

        return Stat::make('Total Notas de Venta', 'S/ ' . number_format($total, 2))
            ->description('Notas de venta emitidas')
            ->descriptionIcon('heroicon-m-document-text')
            ->color('warning')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-200'
            ]);
    }

    // 🧾 TOTAL BOLETAS
    private function getBoletasStat(Carbon $date): Stat
    {
        $total = Invoice::whereDate('created_at', $date)
            ->where('invoice_type', 'receipt')
            ->whereNotNull('order_id') // Boletas tienen order_id
            ->where('tax_authority_status', '!=', 'voided')
            ->sum('total');

        return Stat::make('Total Boletas', 'S/ ' . number_format($total, 2))
            ->description('Boletas electrónicas')
            ->descriptionIcon('heroicon-m-receipt-percent')
            ->color('info')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-cyan-50 to-cyan-100 border-cyan-200'
            ]);
    }

    // 📄 TOTAL FACTURAS
    private function getFacturasStat(Carbon $date): Stat
    {
        $total = Invoice::whereDate('created_at', $date)
            ->where('invoice_type', 'invoice')
            ->where('tax_authority_status', '!=', 'voided')
            ->sum('total');

        return Stat::make('Total Facturas', 'S/ ' . number_format($total, 2))
            ->description('Facturas electrónicas')
            ->descriptionIcon('heroicon-m-document-check')
            ->color('purple')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200'
            ]);
    }

    // ❌ TOTAL ANULADOS
    private function getAnuladosStat(Carbon $date): Stat
    {
        $total = Invoice::whereDate('created_at', $date)
            ->where('tax_authority_status', 'voided')
            ->sum('total');

        return Stat::make('Total Anulados', 'S/ ' . number_format($total, 2))
            ->description('Documentos anulados')
            ->descriptionIcon('heroicon-m-x-circle')
            ->color('danger')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-red-50 to-red-100 border-red-200'
            ]);
    }

    // 🍽️ TOTAL VENTA MESA
    private function getMesaSalesStat(Carbon $startDate, Carbon $endDate): Stat
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('service_type', 'dine_in')
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $dateRange = $this->getDateRangeDescription($startDate, $endDate);

        return Stat::make('Total Venta Mesa', 'S/ ' . number_format($total, 2))
            ->description("Ventas en mesa {$dateRange}")
            ->descriptionIcon('heroicon-m-home')
            ->color('emerald')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-emerald-50 to-emerald-100 border-emerald-200'
            ]);
    }

    // 🥡 TOTAL PARA LLEVAR
    private function getTakeawaySalesStat(Carbon $date): Stat
    {
        $total = Order::whereDate('created_at', $date)
            ->where('service_type', 'takeout')
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return Stat::make('Total Para Llevar', 'S/ ' . number_format($total, 2))
            ->description('Ventas para llevar')
            ->descriptionIcon('heroicon-m-shopping-bag')
            ->color('orange')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200'
            ]);
    }

    // 🚚 TOTAL DELIVERY
    private function getDeliverySalesStat(Carbon $date): Stat
    {
        $total = Order::whereDate('created_at', $date)
            ->where('service_type', 'delivery')
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return Stat::make('Total Delivery', 'S/ ' . number_format($total, 2))
            ->description('Ventas delivery')
            ->descriptionIcon('heroicon-m-truck')
            ->color('indigo')
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-200'
            ]);
    }

    /**
     * 🎯 CALCULAR RANGO DE FECHAS SEGÚN SELECCIÓN
     */
    private function getDateRange(string $range, ?string $startDate, ?string $endDate): array
    {
        $now = Carbon::now();
        
        switch ($range) {
            case 'yesterday':
                return [
                    'start' => Carbon::yesterday()->startOfDay(),
                    'end' => Carbon::yesterday()->endOfDay(),
                ];
            case 'last_7_days':
                return [
                    'start' => Carbon::today()->subDays(6)->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            case 'last_30_days':
                return [
                    'start' => Carbon::today()->subDays(29)->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            case 'this_month':
                return [
                    'start' => Carbon::today()->startOfMonth()->startOfDay(),
                    'end' => Carbon::today()->endOfMonth()->endOfDay(),
                ];
            case 'last_month':
                return [
                    'start' => Carbon::today()->subMonth()->startOfMonth()->startOfDay(),
                    'end' => Carbon::today()->subMonth()->endOfMonth()->endOfDay(),
                ];
            case 'custom':
                if ($startDate && $endDate) {
                    return [
                        'start' => Carbon::parse($startDate)->startOfDay(),
                        'end' => Carbon::parse($endDate)->endOfDay(),
                    ];
                }
                // Si no hay fechas personalizadas, usar hoy
                return [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ];
            default: // today
                return [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ];
        }
    }

    /**
     * 📝 GENERAR DESCRIPCIÓN DEL RANGO DE FECHAS
     */
    private function getDateRangeDescription(Carbon $startDate, Carbon $endDate): string
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($startDate->isSameDay($today) && $endDate->isSameDay($today)) {
            return 'del día';
        }

        if ($startDate->isSameDay($yesterday) && $endDate->isSameDay($yesterday)) {
            return 'de ayer';
        }

        if ($startDate->isSameDay($today->subDays(6)) && $endDate->isSameDay($today)) {
            return 'de los últimos 7 días';
        }

        if ($startDate->isSameDay($today->subDays(29)) && $endDate->isSameDay($today)) {
            return 'de los últimos 30 días';
        }

        if ($startDate->isSameDay($today->startOfMonth()) && $endDate->isSameDay($today->endOfMonth())) {
            return 'del mes';
        }

        if ($startDate->isSameDay($today->subMonth()->startOfMonth()) && $endDate->isSameDay($today->subMonth()->endOfMonth())) {
            return 'del mes pasado';
        }

        if ($startDate->isSameDay($endDate)) {
            return 'del ' . $startDate->format('d/m/Y');
        }

        return 'del ' . $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y');
    }

    /**
     * 📈 Gráfico simple de ventas de los últimos 7 días
     */
    private function getSalesChart(): array
    {
        $sales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailySales = Order::whereDate('created_at', $date)
                ->where('billed', true)
                ->sum('total');
            $sales[] = (float) $dailySales;
        }

        return $sales;
    }
}
