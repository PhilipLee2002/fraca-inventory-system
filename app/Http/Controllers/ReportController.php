<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Alert;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Sales report with filters
     */
    public function salesReport(Request $request)
    {
        // Authorization check
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Sale::with(['customer', 'items.product'])
            ->where('status', 'completed');

        // Apply date filters
        if ($request->filled('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }

        // Apply customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Apply payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->paginate($request->input('per_page', 50));

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalItems = $sales->sum(function($sale) {
            return $sale->items->sum('quantity');
        });

        // Get top selling products for this period
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->when($request->filled('start_date'), function($q) use ($request) {
                $q->where('sales.sale_date', '>=', $request->start_date);
            })
            ->when($request->filled('end_date'), function($q) use ($request) {
                $q->where('sales.sale_date', '<=', $request->end_date);
            })
            ->select('products.name', 'products.sku', 
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'sales' => $sales,
            'summary' => [
                'total_sales' => $totalSales,
                'total_items_sold' => $totalItems,
                'average_sale_value' => $sales->count() > 0 ? round($totalSales / $sales->count(), 2) : 0,
                'total_transactions' => $sales->count()
            ],
            'top_products' => $topProducts,
            'filters' => $request->only(['start_date', 'end_date', 'customer_id', 'payment_method'])
        ]);
    }

    /**
     * Inventory valuation report
     */
    public function inventoryValuation(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Product::with(['category', 'supplier']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('current_stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', '=', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('current_stock', '<=', 'reorder_level')
                        ->where('current_stock', '>', 0);
                    break;
            }
        }

        $products = $query->orderBy('name')->get();

        $totalValuation = 0;
        $totalItems = 0;
        $totalCost = 0;

        foreach ($products as $product) {
            $product->valuation = $product->current_stock * $product->cost_price;
            $product->potential_revenue = $product->current_stock * $product->selling_price;
            $product->potential_profit = $product->potential_revenue - $product->valuation;
            
            $totalValuation += $product->valuation;
            $totalItems += $product->current_stock;
            $totalCost += $product->cost_price;
        }

        return response()->json([
            'products' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'total_items_in_stock' => $totalItems,
                'total_valuation' => round($totalValuation, 2),
                'average_cost_per_item' => $totalItems > 0 ? round($totalValuation / $totalItems, 2) : 0,
                'total_potential_revenue' => round($products->sum('potential_revenue'), 2),
                'total_potential_profit' => round($products->sum('potential_profit'), 2)
            ]
        ]);
    }

    /**
     * Stock movement report for a specific product
     */
    public function stockMovement(Request $request, Product $product)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        $query = $product->stockMovements()->with(['user', 'sale', 'purchase']);

        // Date filters
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Movement type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->latest()->paginate($request->input('per_page', 50));

        // Calculate summary
        $totalAdded = $movements->where('quantity', '>', 0)->sum('quantity');
        $totalRemoved = abs($movements->where('quantity', '<', 0)->sum('quantity'));

        return response()->json([
            'product' => $product->load(['category', 'supplier']),
            'movements' => $movements,
            'summary' => [
                'current_stock' => $product->current_stock,
                'reorder_level' => $product->reorder_level,
                'total_stock_added' => $totalAdded,
                'total_stock_removed' => $totalRemoved,
                'net_stock_change' => $totalAdded - $totalRemoved
            ]
        ]);
    }

    /**
     * Dashboard statistics
     */
    public function dashboardStats()
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $monthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Today's sales
        $todaySales = Sale::whereDate('sale_date', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Yesterday's sales for comparison
        $yesterdaySales = Sale::whereDate('sale_date', $yesterday)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Monthly sales
        $monthlySales = Sale::where('sale_date', '>=', $monthStart)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Last month sales for comparison
        $lastMonthSales = Sale::whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Today's purchases
        $todayPurchases = Purchase::whereDate('purchase_date', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Low stock count
        $lowStockCount = Product::whereColumn('current_stock', '<=', 'reorder_level')
            ->where('current_stock', '>', 0)
            ->count();

        // Out of stock count
        $outOfStockCount = Product::where('current_stock', 0)->count();

        // Recent alerts
        $recentAlerts = Alert::where('resolved', false)
            ->with('product')
            ->latest()
            ->limit(10)
            ->get();

        // Top selling products today
        $topProductsToday = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', $today)
            ->where('sales.status', 'completed')
            ->select('products.name', 'products.sku', 
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // Customer count
        $customerCount = Customer::count();
        $activeCustomers = Customer::has('sales', '>', 0)->count();

        // Supplier count
        $supplierCount = Supplier::count();
        $activeSuppliers = Supplier::has('purchases', '>', 0)->count();

        return response()->json([
            'stats' => [
                // Sales
                'today_sales' => round($todaySales, 2),
                'yesterday_sales' => round($yesterdaySales, 2),
                'sales_change_percent' => $yesterdaySales > 0 
                    ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 2)
                    : ($todaySales > 0 ? 100 : 0),
                'monthly_sales' => round($monthlySales, 2),
                'last_month_sales' => round($lastMonthSales, 2),
                'monthly_sales_change_percent' => $lastMonthSales > 0
                    ? round((($monthlySales - $lastMonthSales) / $lastMonthSales) * 100, 2)
                    : ($monthlySales > 0 ? 100 : 0),
                
                // Purchases
                'today_purchases' => round($todayPurchases, 2),
                
                // Inventory
                'low_stock_items' => $lowStockCount,
                'out_of_stock_items' => $outOfStockCount,
                'total_products' => Product::count(),
                'total_items_in_stock' => Product::sum('current_stock'),
                'inventory_valuation' => round(Product::sum(DB::raw('current_stock * cost_price')), 2),
                
                // Customers & Suppliers
                'total_customers' => $customerCount,
                'active_customers' => $activeCustomers,
                'total_suppliers' => $supplierCount,
                'active_suppliers' => $activeSuppliers,
                
                // Alerts
                'active_alerts' => $recentAlerts->count()
            ],
            'recent_alerts' => $recentAlerts,
            'top_products_today' => $topProductsToday
        ]);
    }

    /**
     * Profit and Loss report
     */
    public function profitLossReport(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        // Default to current month if no dates provided
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();

        // Total Revenue from sales
        $totalRevenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Total Cost of Goods Sold (COGS)
        // This would require calculating cost of each sold item
        // For simplicity, we'll estimate based on average cost price
        $cogs = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Total Purchases (inventory cost)
        $totalPurchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');

        // Gross Profit
        $grossProfit = $totalRevenue - $cogs;

        // Gross Profit Margin
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return response()->json([
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'revenue' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_sales_count' => Sale::whereBetween('sale_date', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->count()
            ],
            'costs' => [
                'cost_of_goods_sold' => round($cogs, 2),
                'total_purchases' => round($totalPurchases, 2)
            ],
            'profit' => [
                'gross_profit' => round($grossProfit, 2),
                'gross_profit_margin' => round($grossProfitMargin, 2) . '%'
            ]
        ]);
    }

    /**
     * Export sales report to CSV
     */
    public function exportSalesReport(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Sale::with(['customer', 'items.product'])
            ->where('status', 'completed');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }

        $sales = $query->latest()->get();

        $filename = 'sales-report-' . date('Y-m-d-H-i-s') . '.csv';
        
        return response()->streamDownload(function() use ($sales) {
            $handle = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($handle, [
                'Invoice Number', 'Date', 'Customer', 'Items', 'Total Amount', 
                'Payment Method', 'Status'
            ]);
            
            // Data rows
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sale_date,
                    $sale->customer ? $sale->customer->name : 'Walk-in',
                    $sale->items->count(),
                    $sale->total_amount,
                    $sale->payment_method,
                    $sale->status
                ]);
            }
            
            fclose($handle);
        }, $filename);
    }
}