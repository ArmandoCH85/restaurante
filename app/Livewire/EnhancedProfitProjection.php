<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Recipe;
use App\Models\RecipeDetail;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnhancedProfitProjection extends Component
{
    public $selectedMonth;
    public $selectedYear;
    public $selectedCategory = null;
    public $profitData = [];
    public $totalRevenue = 0;
    public $totalCost = 0;
    public $totalProfit = 0;
    public $profitMargin = 0;
    public $categories = [];
    public $monthlyTrend = [];
    public $topProducts = [];
    public $monthNames = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    public function mount()
    {
        // Set default values to current month and year
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
        
        // Load categories for filter
        $this->categories = ProductCategory::orderBy('name')->get();
        
        // Calculate initial profit projection
        $this->calculateProfitProjection();
        
        // Calculate monthly trend for the last 6 months
        $this->calculateMonthlyTrend();
        
        // Get top 5 most profitable products
        $this->calculateTopProducts();
    }

    public function calculateProfitProjection()
    {
        // Reset totals
        $this->totalRevenue = 0;
        $this->totalCost = 0;
        $this->totalProfit = 0;
        $this->profitMargin = 0;
        $this->profitData = [];

        // Get start and end dates for the selected month
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();

        // Base query for orders in the selected month
        $orderQuery = Order::whereBetween('order_datetime', [$startDate, $endDate])
            ->where('status', 'completed');

        // Get all order details for the period
        $orderDetails = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('order_datetime', [$startDate, $endDate])
                  ->where('status', 'completed');
        })
        ->with(['product', 'product.category', 'product.recipe.details.ingredient'])
        ->get();

        // Filter by category if selected
        if ($this->selectedCategory) {
            $orderDetails = $orderDetails->filter(function ($detail) {
                return $detail->product->category_id == $this->selectedCategory;
            });
        }

        // Group by product for analysis
        $productGroups = $orderDetails->groupBy('product_id');

        foreach ($productGroups as $productId => $details) {
            $product = $details->first()->product;
            
            // Skip if filtered by category and doesn't match
            if ($this->selectedCategory && $product->category_id != $this->selectedCategory) {
                continue;
            }

            // Calculate revenue for this product
            $quantity = $details->sum('quantity');
            $revenue = $details->sum('subtotal');
            $this->totalRevenue += $revenue;

            // Calculate cost based on recipe if available
            $cost = 0;
            if ($product->has_recipe) {
                $recipe = Recipe::where('product_id', $productId)->first();
                if ($recipe) {
                    $recipeDetails = RecipeDetail::where('recipe_id', $recipe->id)->get();
                    foreach ($recipeDetails as $ingredient) {
                        // Get the latest purchase cost for this ingredient
                        $latestPurchase = PurchaseDetail::where('product_id', $ingredient->ingredient_id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        $ingredientCost = $latestPurchase ? $latestPurchase->unit_cost : $product->current_cost;
                        $cost += $ingredientCost * $ingredient->quantity * $quantity;
                    }
                } else {
                    // If no recipe found but has_recipe is true, use current_cost
                    $cost = $product->current_cost * $quantity;
                }
            } else {
                // If no recipe, use current_cost
                $cost = $product->current_cost * $quantity;
            }

            $this->totalCost += $cost;
            $profit = $revenue - $cost;
            $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // Add to profit data array
            $this->profitData[] = [
                'product_id' => $productId,
                'product_name' => $product->name,
                'category' => $product->category->name,
                'quantity' => $quantity,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'profit_margin' => $profitMargin
            ];
        }

        // Sort by profit (highest first)
        usort($this->profitData, function ($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        // Calculate totals
        $this->totalProfit = $this->totalRevenue - $this->totalCost;
        $this->profitMargin = $this->totalRevenue > 0 ? ($this->totalProfit / $this->totalRevenue) * 100 : 0;
    }
    
    public function calculateMonthlyTrend()
    {
        $this->monthlyTrend = [];
        
        // Get data for the last 6 months
        $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
        $startDate = $endDate->copy()->subMonths(5)->startOfMonth();
        
        for ($i = 0; $i < 6; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            // Calculate revenue for this month
            $revenue = Order::whereBetween('order_datetime', [$monthStart, $monthEnd])
                ->where('status', 'completed')
                ->sum('total');
                
            // Calculate cost for this month (simplified version)
            $cost = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereBetween('orders.order_datetime', [$monthStart, $monthEnd])
                ->where('orders.status', 'completed')
                ->selectRaw('SUM(order_details.quantity * products.current_cost) as total_cost')
                ->value('total_cost') ?? 0;
                
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
            
            $this->monthlyTrend[] = [
                'month' => $this->monthNames[$currentMonth->month],
                'year' => $currentMonth->year,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'margin' => $margin
            ];
        }
    }
    
    public function calculateTopProducts()
    {
        // Get start and end dates for the selected month
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();
        
        // Get top 5 products by profit
        $this->topProducts = collect($this->profitData)
            ->sortByDesc('profit')
            ->take(5)
            ->values()
            ->all();
    }

    public function updatedSelectedMonth()
    {
        $this->calculateProfitProjection();
        $this->calculateMonthlyTrend();
        $this->calculateTopProducts();
    }

    public function updatedSelectedYear()
    {
        $this->calculateProfitProjection();
        $this->calculateMonthlyTrend();
        $this->calculateTopProducts();
    }

    public function updatedSelectedCategory()
    {
        $this->calculateProfitProjection();
        $this->calculateTopProducts();
    }

    public function render()
    {
        return view('livewire.enhanced-profit-projection');
    }
}