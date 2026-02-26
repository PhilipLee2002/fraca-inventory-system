<?php
// app/Http/Controllers/SaleController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SaleController extends Controller
{
    // List all sales (API endpoint)
    public function index(Request $request)
    {
        if (!Gate::allows('manage-sales')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Sale::with(['customer', 'items.product'])
            ->where('status', 'completed');

        // Date filters
        if ($request->filled('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $sales = $query->latest()->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $sales,
            'message' => 'Sales retrieved successfully'
        ]);
    }

    // Show single sale (API endpoint)
    public function show(Sale $sale)
    {
        if (!Gate::allows('manage-sales')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sale->load(['customer', 'items.product']);

        return response()->json([
            'success' => true,
            'data' => $sale,
            'message' => 'Sale retrieved successfully'
        ]);
    }

    // Create new sale (API endpoint - MAIN FUNCTIONALITY)
    public function store(Request $request)
    {
        if (!Gate::allows('manage-sales')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,mobile_money',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Check stock availability for ALL items first
            $outOfStockItems = [];
            foreach ($validated['items'] as $index => $item) {
                $product = Product::find($item['product_id']);
                if ($product->current_stock < $item['quantity']) {
                    $outOfStockItems[] = [
                        'product' => $product->name,
                        'available' => $product->current_stock,
                        'requested' => $item['quantity']
                    ];
                }
            }

            if (!empty($outOfStockItems)) {
                throw new \Exception("Insufficient stock for items: " . 
                    json_encode($outOfStockItems));
            }

            // Step 2: Calculate totals
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Step 3: Generate invoice number
            $invoiceNumber = 'SALE-' . date('Ymd') . '-' . strtoupper(uniqid());

            // Step 4: Create sale record
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'] ?? null,
                'sale_date' => $validated['sale_date'],
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
                'created_by' => auth()->id()
            ]);

            // Step 5: Create sale items and update stock
            foreach ($validated['items'] as $item) {
                // Create sale item
                $saleItem = $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price']
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->decrement('current_stock', $item['quantity']);

                // Log stock movement
                $product->logStockMovement('sale', -$item['quantity'], 
                    "Sale #{$invoiceNumber}");
            }

            DB::commit();

            // Load relationships for response
            $sale->load(['customer', 'items.product']);

            return response()->json([
                'success' => true,
                'data' => $sale,
                'message' => 'Sale created successfully. Stock updated.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create sale'
            ], 422);
        }
    }

    // Cancel/void a sale (API endpoint)
    public function destroy(Sale $sale)
    {
        if (!Gate::allows('manage-sales')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($sale->status !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Sale is not in a valid state for cancellation'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Return stock for each item
            foreach ($sale->items as $item) {
                $product = $item->product;
                $product->increment('current_stock', $item->quantity);
                
                // Log stock movement (return)
                $product->logStockMovement('return', $item->quantity, 
                    "Sale cancellation #{$sale->invoice_number}");
            }

            // Update sale status
            $sale->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale cancelled successfully. Stock returned.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to cancel sale'
            ], 422);
        }
    }

    // Get sale statistics (API endpoint)
    public function stats(Request $request)
    {
        if (!Gate::allows('manage-sales')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $today = now()->today();
        $yesterday = now()->yesterday();
        $monthStart = now()->startOfMonth();

        // Today's sales
        $todaySales = Sale::whereDate('sale_date', $today)
            ->where('status', 'completed')
            ->count();

        $todayRevenue = Sale::whereDate('sale_date', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        // This month's sales
        $monthSales = Sale::where('sale_date', '>=', $monthStart)
            ->where('status', 'completed')
            ->count();

        $monthRevenue = Sale::where('sale_date', '>=', $monthStart)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Top selling products this month
        $topProducts = SaleItem::selectRaw('
                products.id,
                products.name,
                products.sku,
                SUM(sale_items.quantity) as total_sold,
                SUM(sale_items.total_price) as total_revenue
            ')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.sale_date', '>=', $monthStart)
            ->where('sales.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'sales_count' => $todaySales,
                    'total_revenue' => $todayRevenue
                ],
                'this_month' => [
                    'sales_count' => $monthSales,
                    'total_revenue' => $monthRevenue
                ],
                'top_products' => $topProducts
            ]
        ]);
    }
}