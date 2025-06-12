<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Carbon\Carbon;
use App\Models\Invoice;

class SalesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // �� GRID RESPONSIVO - 5 WIDGETS POR FILA
    protected int | string | array $columnSpan = [
        'default' => 'full',  // Móvil: ancho completo
        'sm' => 'full',       // Tablet pequeña: ancho completo
        'md' => 2.4,          // Desktop pequeño: 2.4 columnas (5 por fila)
        'lg' => 2.4,          // Desktop: 2.4 columnas (5 por fila)
        'xl' => 2.4,          // Desktop grande: 2.4 columnas (5 por fila)
        '2xl' => 2.4,         // Desktop extra: 2.4 columnas (5 por fila)
    ];

    protected function getStats(): array
    {
        // 📅 FILTRO TEMPORAL - HOY POR DEFECTO
        $today = Carbon::today();

        return [
            // 📊 FILA 1: OPERACIONES Y VENTAS PRINCIPALES (5 widgets)
            $this->getOperationsCountStat($today),
            $this->getTotalSalesStat($today),
            $this->getSalesNotesStat($today),
            $this->getBoletasStat($today),
            $this->getFacturasStat($today),

            // 📊 FILA 2: ANULADOS Y TIPOS DE SERVICIO (4 widgets)
            $this->getAnuladosStat($today),
            $this->getMesaSalesStat($today),
            $this->getTakeawaySalesStat($today),
            $this->getDeliverySalesStat($today),
        ];
    }

    // 🔢 N° OPERACIONES
    private function getOperationsCountStat(Carbon $date): Stat
    {
        $count = Order::whereDate('created_at', $date)
            ->where('status', '!=', 'cancelled')
            ->count();

        return Stat::make('N° Operaciones', number_format($count))
            ->description('Órdenes procesadas hoy')
            ->descriptionIcon('heroicon-m-calculator')
            ->color('primary')
            ->chart([7, 12, 8, 15, 10, 18, $count])
            ->extraAttributes([
                'class' => 'bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200'
            ]);
    }

    // 💰 TOTAL VENTAS
    private function getTotalSalesStat(Carbon $date): Stat
    {
        $total = Order::whereDate('created_at', $date)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return Stat::make('Total Ventas', 'S/ ' . number_format($total, 2))
            ->description('Ingresos del día')
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
    private function getMesaSalesStat(Carbon $date): Stat
    {
        $total = Order::whereDate('created_at', $date)
            ->where('service_type', 'dine_in')
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return Stat::make('Total Venta Mesa', 'S/ ' . number_format($total, 2))
            ->description('Ventas en mesa')
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
