<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Alert;
use App\Models\StockHistory;
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
     * Profit & Loss report by period
     */
    public function profitLoss(Request $request)
    {
        try {
            $start = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->toDateString();
            $end   = $request->filled('end_date')   ? $request->end_date   : now()->toDateString();

            $revenue  = Sale::where('status', 'completed')
                ->whereBetween('sale_date', [$start, $end])->sum('total_amount');
            $expenses = Purchase::where('status', 'received')
                ->whereBetween('purchase_date', [$start, $end])->sum('total_amount');
            $profit   = $revenue - $expenses;
            $margin   = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;

            // Daily breakdown
            $dailySales = Sale::where('status', 'completed')
                ->whereBetween('sale_date', [$start, $end])
                ->selectRaw('sale_date as date, SUM(total_amount) as revenue, COUNT(*) as transactions')
                ->groupBy('sale_date')->orderBy('sale_date')->get();

            $dailyPurchases = Purchase::where('status', 'received')
                ->whereBetween('purchase_date', [$start, $end])
                ->selectRaw('purchase_date as date, SUM(total_amount) as expenses')
                ->groupBy('purchase_date')->orderBy('purchase_date')->get()
                ->keyBy('date');

            $breakdown = $dailySales->map(fn($row) => [
                'date'         => $row->date,
                'revenue'      => round($row->revenue, 2),
                'expenses'     => round($dailyPurchases[$row->date]->expenses ?? 0, 2),
                'profit'       => round($row->revenue - ($dailyPurchases[$row->date]->expenses ?? 0), 2),
                'transactions' => $row->transactions,
            ]);

            return $this->sendSuccess([
                'summary'   => compact('revenue', 'expenses', 'profit', 'margin'),
                'breakdown' => $breakdown,
                'period'    => compact('start', 'end'),
            ], 'Profit & Loss report retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error generating P&L report: ' . $e->getMessage());
        }
    }

    /**
     * Stock movement report
     */
    public function stockMovement(Request $request)
    {
        try {
            $query = StockHistory::with('product:id,name,sku');

            if ($request->filled('start_date')) {
                $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
            }
            if ($request->filled('end_date')) {
                $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
            }
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }
            if ($request->filled('transaction_type')) {
                $query->where('transaction_type', $request->transaction_type);
            }

            $movements = $query->latest()->paginate($request->input('per_page', 50));

            $summary = [
                'total_in'  => StockHistory::where('quantity_change', '>', 0)->sum('quantity_change'),
                'total_out' => abs(StockHistory::where('quantity_change', '<', 0)->sum('quantity_change')),
            ];

            return $this->sendSuccess([
                'movements' => $movements,
                'summary'   => $summary,
            ], 'Stock movement report retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error generating stock movement report: ' . $e->getMessage());
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

            // Monthly purchases
            $monthlyPurchases = Purchase::where('purchase_date', '>=', $monthStart)
                ->where('status', 'received')
                ->sum('total_amount');

            // Profit margin (monthly)
            $profitMargin = $monthlySales > 0
                ? round((($monthlySales - $monthlyPurchases) / $monthlySales) * 100, 1)
                : 0;

            // Low stock count
            $lowStockCount = Product::whereColumn('current_stock', '<=', 'reorder_level')
                ->where('current_stock', '>', 0)
                ->count();

            // Out of stock count
            $outOfStockCount = Product::where('current_stock', 0)->count();

            // Pending counts
            $pendingSales = Sale::where('status', 'pending')->count();
            $pendingPurchases = Purchase::where('status', 'pending')->count();

            // Active alerts — count directly from products for accuracy
            $activeAlertsCount = Product::where(function ($q) {
                $q->where('current_stock', 0)
                  ->orWhereColumn('current_stock', '<=', 'reorder_level');
            })->count();

            // Top performers (top 5 users by completed sales this month)
            $topPerformers = \App\Models\User::select('users.id', 'users.name')
                ->selectRaw('COUNT(sales.id) as sales_count, COALESCE(SUM(sales.total_amount), 0) as total_amount')
                ->leftJoin('sales', function ($join) use ($monthStart) {
                    $join->on('sales.user_id', '=', 'users.id')
                        ->where('sales.status', 'completed')
                        ->where('sales.sale_date', '>=', $monthStart);
                })
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get();

            // Financial summary (current month)
            $monthlyRevenue = $monthlySales;
            $monthlyExpenses = $monthlyPurchases;
            $monthlyProfit = $monthlyRevenue - $monthlyExpenses;

            // Inventory valuation
            $inventoryValuation = Product::selectRaw('COALESCE(SUM(current_stock * selling_price), 0) as total')
                ->value('total');

            // Recent stock adjustments count (last 7 days)
            $recentAdjustments = StockHistory::where('transaction_type', 'adjustment')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count();

            return $this->sendSuccess([
                'stats' => [
                    'today_sales'          => round($todaySales, 2),
                    'monthly_sales'        => round($monthlySales, 2),
                    'monthly_purchases'    => round($monthlyPurchases, 2),
                    'profit_margin'        => $profitMargin,
                    'sales_vs_target'      => 0, // placeholder — no target model yet
                    'low_stock_items'      => $lowStockCount,
                    'out_of_stock_items'   => $outOfStockCount,
                    'overstock'            => 0, // placeholder
                    'stock_turnover'       => 0, // placeholder
                    'inventory_valuation'  => round($inventoryValuation, 2),
                    'total_products'       => Product::count(),
                    'total_customers'      => Customer::count(),
                    'total_suppliers'      => Supplier::count(),
                    'total_users'          => \App\Models\User::count(),
                    'active_alerts'        => $activeAlertsCount,
                    'pending_sales'        => $pendingSales,
                    'pending_purchases'    => $pendingPurchases,
                    'recent_adjustments'   => $recentAdjustments,
                    // Financial summary
                    'monthly_revenue'      => round($monthlyRevenue, 2),
                    'monthly_expenses'     => round($monthlyExpenses, 2),
                    'monthly_profit'       => round($monthlyProfit, 2),
                ],
                'top_performers' => $topPerformers,
            ], 'Dashboard statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving dashboard statistics: ' . $e->getMessage());
        }
    }

    /**
     * Recent activity timeline (last 10 events across sales, purchases, adjustments)
     */
    public function recentActivity()
    {
        try {
            $activities = collect();

            // Recent sales
            Sale::with('user:id,name', 'customer:id,first_name,last_name')
                ->latest()
                ->limit(5)
                ->get()
                ->each(function ($sale) use (&$activities) {
                    $customerName = $sale->customer
                        ? trim($sale->customer->first_name . ' ' . $sale->customer->last_name)
                        : 'Unknown';
                    $activities->push([
                        'type'      => 'sale',
                        'icon'      => 'fa-shopping-cart',
                        'color'     => 'success',
                        'action'    => 'New sale created',
                        'entity'    => $sale->invoice_number,
                        'detail'    => "Customer: {$customerName}",
                        'amount'    => $sale->total_amount,
                        'user'      => $sale->user->name ?? 'System',
                        'timestamp' => $sale->created_at->toIso8601String(),
                        'url'       => '/sales',
                    ]);
                });

            // Recent purchases
            Purchase::with('user:id,name', 'supplier:id,name')
                ->latest()
                ->limit(5)
                ->get()
                ->each(function ($purchase) use (&$activities) {
                    $activities->push([
                        'type'      => 'purchase',
                        'icon'      => 'fa-shopping-bag',
                        'color'     => 'warning',
                        'action'    => 'Purchase order created',
                        'entity'    => $purchase->purchase_number,
                        'detail'    => "Supplier: " . ($purchase->supplier->name ?? 'Unknown'),
                        'amount'    => $purchase->total_amount,
                        'user'      => $purchase->user->name ?? 'System',
                        'timestamp' => $purchase->created_at->toIso8601String(),
                        'url'       => '/purchases',
                    ]);
                });

            // Recent stock adjustments
            StockHistory::with('product:id,name')
                ->where('transaction_type', 'adjustment')
                ->latest()
                ->limit(5)
                ->get()
                ->each(function ($adj) use (&$activities) {
                    $activities->push([
                        'type'      => 'adjustment',
                        'icon'      => 'fa-sliders-h',
                        'color'     => $adj->quantity_change >= 0 ? 'info' : 'danger',
                        'action'    => 'Stock adjusted',
                        'entity'    => $adj->product->name ?? 'Unknown product',
                        'detail'    => ($adj->quantity_change >= 0 ? '+' : '') . $adj->quantity_change . ' units',
                        'amount'    => null,
                        'user'      => 'System',
                        'timestamp' => $adj->created_at->toIso8601String(),
                        'url'       => '/stock-adjustments',
                    ]);
                });

            // Sort by timestamp desc, take 10
            $sorted = $activities->sortByDesc('timestamp')->values()->take(10);

            return $this->sendSuccess($sorted, 'Recent activity retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving recent activity: ' . $e->getMessage());
        }
    }

    /**
     * Active alerts — reads from alerts table, but falls back to live product
     * stock check so the widget is always accurate even if no alert records exist.
     */
    public function alerts()
    {
        try {
            // First, sync any currently low/out-of-stock products that have no alert yet
            $lowStockProducts = Product::where('current_stock', '>', 0)
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->get();

            foreach ($lowStockProducts as $product) {
                Alert::createForProduct($product, 'low_stock');
            }

            $outOfStockProducts = Product::where('current_stock', 0)->get();

            foreach ($outOfStockProducts as $product) {
                Alert::createForProduct($product, 'out_of_stock');
            }

            // Now fetch all unread alerts
            $alerts = Alert::where('is_read', false)
                ->with('product:id,name,current_stock,reorder_level')
                ->latest()
                ->get()
                ->map(function ($alert) {
                    return [
                        'id'            => $alert->id,
                        'type'          => $alert->type,
                        'message'       => $alert->message,
                        'product_name'  => $alert->product?->name,
                        'current_stock' => $alert->product?->current_stock,
                        'reorder_level' => $alert->product?->reorder_level,
                        'created_at'    => $alert->created_at->toIso8601String(),
                    ];
                });

            return $this->sendSuccess($alerts, 'Alerts retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving alerts: ' . $e->getMessage());
        }
    }

    /**
     * Mark an alert as read.
     */
    public function markAlertRead($id)
    {
        try {
            $alert = Alert::findOrFail($id);
            $alert->update(['is_read' => true]);
            return $this->sendSuccess(null, 'Alert marked as read');
        } catch (\Exception $e) {
            return $this->sendError('Error updating alert: ' . $e->getMessage());
        }
    }
}
