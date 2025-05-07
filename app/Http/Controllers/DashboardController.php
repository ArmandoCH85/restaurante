<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\Customer;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con estadísticas
     */
    public function index(Request $request)
    {
        // Período de tiempo (hoy, esta semana, este mes, personalizado)
        $period = $request->input('period', 'today');
        $startDate = null;
        $endDate = null;
        
        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->input('start_date')) 
                    : Carbon::today()->subDays(30);
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->input('end_date'))->endOfDay() 
                    : Carbon::today()->endOfDay();
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
        }
        
        // Estadísticas generales
        $stats = $this->getGeneralStats($startDate, $endDate);
        
        // Ventas por hora
        $hourlyStats = $this->getHourlySales($startDate, $endDate);
        
        // Productos más vendidos
        $topProducts = $this->getTopProducts($startDate, $endDate);
        
        // Estadísticas de mesas
        $tableStats = $this->getTableStats($startDate, $endDate);
        
        // Clientes frecuentes
        $topCustomers = $this->getTopCustomers($startDate, $endDate);
        
        return view('dashboard.index', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'hourlyStats' => $hourlyStats,
            'topProducts' => $topProducts,
            'tableStats' => $tableStats,
            'topCustomers' => $topCustomers
        ]);
    }
    
    /**
     * Obtiene estadísticas generales
     */
    private function getGeneralStats(Carbon $startDate, Carbon $endDate)
    {
        // Total de ventas
        $totalSales = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->sum('total');
            
        // Número de órdenes
        $orderCount = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->count();
            
        // Ticket promedio
        $averageTicket = $orderCount > 0 ? $totalSales / $orderCount : 0;
        
        // Ventas por tipo de servicio
        $salesByServiceType = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select('service_type', DB::raw('SUM(total) as total'))
            ->groupBy('service_type')
            ->get()
            ->pluck('total', 'service_type')
            ->toArray();
            
        // Productos vendidos
        $productsSold = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_datetime', [$startDate, $endDate])
                    ->where('billed', true);
            })
            ->sum('quantity');
            
        return [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageTicket' => $averageTicket,
            'salesByServiceType' => $salesByServiceType,
            'productsSold' => $productsSold
        ];
    }
    
    /**
     * Obtiene ventas por hora
     */
    private function getHourlySales(Carbon $startDate, Carbon $endDate)
    {
        return Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->select(DB::raw('HOUR(order_datetime) as hour'), DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(order_datetime)'))
            ->orderBy('hour')
            ->get();
    }
    
    /**
     * Obtiene los productos más vendidos
     */
    private function getTopProducts(Carbon $startDate, Carbon $endDate, $limit = 10)
    {
        return OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_datetime', [$startDate, $endDate])
                    ->where('billed', true);
            })
            ->select(
                'product_id', 
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->with('product:id,name,category_id,image_path', 'product.category:id,name')
            ->groupBy('product_id')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Obtiene estadísticas de mesas
     */
    private function getTableStats(Carbon $startDate, Carbon $endDate)
    {
        // Mesas más utilizadas
        $topTables = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->whereNotNull('table_id')
            ->select('table_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_sales'))
            ->groupBy('table_id')
            ->with('table:id,number,capacity')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();
            
        // Estado actual de las mesas
        $tableStatus = Table::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
            
        return [
            'topTables' => $topTables,
            'tableStatus' => $tableStatus
        ];
    }
    
    /**
     * Obtiene los clientes más frecuentes
     */
    private function getTopCustomers(Carbon $startDate, Carbon $endDate, $limit = 5)
    {
        return Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('billed', true)
            ->whereNotNull('customer_id')
            ->select(
                'customer_id', 
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('SUM(total) as total_spent')
            )
            ->with('customer:id,name,document_type,document_number')
            ->groupBy('customer_id')
            ->orderByDesc('visit_count')
            ->limit($limit)
            ->get();
    }
}
