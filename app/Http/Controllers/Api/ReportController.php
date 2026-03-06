<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Alert;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseController
{
    /**
     * Sales report with filters
     */
    public function sales(Request $request)
    {
        try {
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

            $sales = $query->latest()->paginate($request->input('per_page', 50));

            // Calculate totals
            $totalSales = Sale::where('status', 'completed')
                ->when($request->filled('start_date'), fn($q) => $q->where('sale_date', '>=', $request->start_date))
                ->when($request->filled('end_date'), fn($q) => $q->where('sale_date', '<=', $request->end_date))
                ->sum('total_amount');

            return $this->sendSuccess([
                'sales' => $sales,
                'summary' => [
                    'total_sales' => round($totalSales, 2),
                    'total_transactions' => $sales->total(),
                    'average_sale_value' => $sales->total() > 0 ? round($totalSales / $sales->total(), 2) : 0,
                ]
            ], 'Sales report retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error generating sales report: ' . $e->getMessage());
        }
    }

    /**
     * Purchase report with filters
     */
    public function purchases(Request $request)
    {
        try {
            $query = Purchase::with(['supplier', 'items.product'])
                ->where('status', 'received');

            // Apply date filters
            if ($request->filled('start_date')) {
                $query->where('purchase_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('purchase_date', '<=', $request->end_date);
            }

            // Apply supplier filter
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            $purchases = $query->latest()->paginate($request->input('per_page', 50));

            // Calculate totals
            $totalPurchases = Purchase::where('status', 'received')
                ->when($request->filled('start_date'), fn($q) => $q->where('purchase_date', '>=', $request->start_date))
                ->when($request->filled('end_date'), fn($q) => $q->where('purchase_date', '<=', $request->end_date))
                ->sum('total_amount');

            return $this->sendSuccess([
                'purchases' => $purchases,
                'summary' => [
                    'total_purchases' => round($totalPurchases, 2),
                    'total_transactions' => $purchases->total(),
                    'average_purchase_value' => $purchases->total() > 0 ? round($totalPurchases / $purchases->total(), 2) : 0,
                ]
            ], 'Purchase report retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error generating purchase report: ' . $e->getMessage());
        }
    }

    /**
     * Stock levels report
     */
    public function stockLevels(Request $request)
    {
        try {
            $query = Product::with(['category', 'supplier']);

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
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

            $products = $query->orderBy('name')->paginate($request->input('per_page', 50));

            // Calculate summary
            $totalProducts = Product::count();
            $inStock = Product::where('current_stock', '>', 0)->count();
            $outOfStock = Product::where('current_stock', '=', 0)->count();
            $lowStock = Product::whereColumn('current_stock', '<=', 'reorder_level')
                ->where('current_stock', '>', 0)
                ->count();

            return $this->sendSuccess([
                'products' => $products,
                'summary' => [
                    'total_products' => $totalProducts,
                    'in_stock' => $inStock,
                    'out_of_stock' => $outOfStock,
                    'low_stock' => $lowStock,
                    'total_items_in_stock' => Product::sum('current_stock'),
                ]
            ], 'Stock levels report retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error generating stock levels report: ' . $e->getMessage());
        }
    }

    /**
     * Inventory valuation report
     */
    public function inventoryValuation(Request $request)
    {
        try {
            $query = Product::with(['category', 'supplier']);

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $products = $query->where('current_stock', '>', 0)->get();

            $totalValuation = 0;
            $totalPotentialRevenue = 0;

            foreach ($products as $product) {
                $product->valuation = $product->current_stock * $product->cost_price;
                $product->potential_revenue = $product->current_stock * $product->selling_price;
                $product->potential_profit = $product->potential_revenue - $product->valuation;
                
                $totalValuation += $product->valuation;
                $totalPotentialRevenue += $product->potential_revenue;
            }

            return $this->sendSuccess([
                'products' => $products,
                'summary' => [
                    'total_products' => $products->count(),
                    'total_items_in_stock' => $products->sum('current_stock'),
                    'total_valuation' => round($totalValuation, 2),
                    'total_potential_revenue' => round($totalPotentialRevenue, 2),
                    'total_potential_profit' => round($totalPotentialRevenue - $totalValuation, 2),
                ]
            ], 'Inventory valuation report retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error generating inventory valuation report: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard statistics
     */
    public function dashboard()
    {
        try {
            $today = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();

            // Today's sales
            $todaySales = Sale::whereDate('sale_date', $today)
                ->where('status', 'completed')
                ->sum('total_amount');

            // Monthly sales
            $monthlySales = Sale::where('sale_date', '>=', $monthStart)
                ->where('status', 'completed')
                ->sum('total_amount');

            // Low stock count
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'reorder_level')
                ->where('current_stock', '>', 0)
                ->count();

            // Out of stock count
            $outOfStockCount = Product::where('current_stock', 0)->count();

            // Recent alerts
            $recentAlerts = Alert::where('is_resolved', false)
                ->with('product')
                ->latest()
                ->limit(10)
                ->get();

            return $this->sendSuccess([
                'stats' => [
                    'today_sales' => round($todaySales, 2),
                    'monthly_sales' => round($monthlySales, 2),
                    'low_stock_items' => $lowStockCount,
                    'out_of_stock_items' => $outOfStockCount,
                    'total_products' => Product::count(),
                    'total_customers' => Customer::count(),
                    'total_suppliers' => Supplier::count(),
                    'active_alerts' => $recentAlerts->count(),
                ],
                'recent_alerts' => $recentAlerts,
            ], 'Dashboard statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving dashboard statistics: ' . $e->getMessage());
        }
    }
}
