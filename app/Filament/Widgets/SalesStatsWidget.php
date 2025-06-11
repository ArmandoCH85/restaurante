<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Carbon\Carbon;

class SalesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // 📐 ANCHO COMPLETO RESPONSIVO SEGÚN FILAMENT DOCS
    protected int | string | array $columnSpan = [
        'default' => 'full',  // Móvil: ancho completo
        'sm' => 'full',       // Tablet: ancho completo
        'md' => 'full',       // Desktop: ancho completo
        'xl' => 'full',       // Desktop grande: ancho completo
        '2xl' => 'full',      // Desktop extra: ancho completo
    ];

    protected function getStats(): array
    {
        // 📊 MÉTRICAS DEL DÍA ACTUAL
        $today = Carbon::today();

        // 💰 VENTAS TOTALES DEL DÍA
        $todaySales = Order::whereDate('created_at', $today)
            ->where('billed', true)
            ->sum('total');

        // 📈 ÓRDENES DEL DÍA
        $todayOrders = Order::whereDate('created_at', $today)->count();

        // 🍽️ VENTAS POR MESA (dine_in)
        $mesaSales = Order::whereDate('created_at', $today)
            ->where('service_type', 'dine_in')
            ->where('billed', true)
            ->sum('total');

        $mesaOrders = Order::whereDate('created_at', $today)
            ->where('service_type', 'dine_in')
            ->count();

        // 🚚 DELIVERY
        $deliverySales = Order::whereDate('created_at', $today)
            ->where('service_type', 'delivery')
            ->where('billed', true)
            ->sum('total');

        $deliveryOrders = Order::whereDate('created_at', $today)
            ->where('service_type', 'delivery')
            ->count();

        // 🥡 VENTA DIRECTA (takeout + sin mesa)
        $directSales = Order::whereDate('created_at', $today)
            ->where(function($query) {
                $query->where('service_type', 'takeout')
                      ->orWhere(function($q) {
                          $q->where('service_type', 'dine_in')
                            ->whereNull('table_id');
                      });
            })
            ->where('billed', true)
            ->sum('total');

        $directOrders = Order::whereDate('created_at', $today)
            ->where(function($query) {
                $query->where('service_type', 'takeout')
                      ->orWhere(function($q) {
                          $q->where('service_type', 'dine_in')
                            ->whereNull('table_id');
                      });
            })
            ->count();

        // 🎯 TICKET PROMEDIO
        $avgTicket = $todayOrders > 0 ? $todaySales / $todayOrders : 0;

        return [
            // 💰 TOTAL DEL DÍA
            Stat::make('💰 Ventas del Día', 'S/ ' . number_format($todaySales, 2))
                ->description($todayOrders . ' órdenes totales')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getSalesChart()),

            // 🍽️ MESA
            Stat::make('🍽️ Ventas Mesa', 'S/ ' . number_format($mesaSales, 2))
                ->description($mesaOrders . ' órdenes')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            // 🚚 DELIVERY
            Stat::make('🚚 Delivery', 'S/ ' . number_format($deliverySales, 2))
                ->description($deliveryOrders . ' pedidos')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            // 🥡 VENTA DIRECTA
            Stat::make('🥡 Venta Directa', 'S/ ' . number_format($directSales, 2))
                ->description($directOrders . ' órdenes')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            // 🎯 TICKET PROMEDIO
            Stat::make('🎯 Ticket Promedio', 'S/ ' . number_format($avgTicket, 2))
                ->description('Por orden del día')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($avgTicket > 50 ? 'success' : ($avgTicket > 30 ? 'warning' : 'danger')),
        ];
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
