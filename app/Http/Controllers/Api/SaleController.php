<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends BaseController
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        try {
            $query = Sale::with(['customer', 'items.product']);

            // Apply filters
            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('sale_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            // Apply sorting
            $query->orderBy('sale_date', 'desc')
                  ->orderBy('created_at', 'desc');

            $sales = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($sales, 'Sales retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving sales: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created sale.
     */
    public function store(StoreSaleRequest $request)
    {
        try {
            DB::beginTransaction();

            // VALIDATION: Check stock FIRST
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->current_stock < $item['quantity']) {
                    throw new \Exception(
                        "Insufficient stock for product: " . ($product->name ?? 'Unknown')
                    );
                }
            }

            // Calculate total amount (simple: no tax/discount/shipping)
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Create sale
            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'invoice_number' => 'INV-' . date('YmdHis'),
                'sale_date' => $request->sale_date,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => $request->status ?? 'pending',
                'notes' => $request->notes,
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock (decrease)
                $product = Product::find($item['product_id']);
                $product->decrement('current_stock', $item['quantity']);

                // Create stock history record
                StockHistory::create([
                    'product_id' => $item['product_id'],
                    'quantity_change' => -$item['quantity'],
                    'transaction_type' => 'sale',
                    'reference_id' => $sale->id,
                    'notes' => "Sale: {$sale->invoice_number}",
                ]);
            }

            DB::commit();

            $sale->load(['customer', 'items.product']);

            return $this->sendCreated($sale, 'Sale created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating sale: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        try {
            $sale->load([
                'customer',
                'items.product',
                'payments',
                'createdBy'
            ]);

            return $this->sendSuccess($sale, 'Sale retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving sale: ' . $e->getMessage());
        }
    }

    /**
     * Update sale status.
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        try {
            $request->validate([
                'status' => 'required|in:completed,cancelled',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $oldStatus = $sale->status;
            $newStatus = $request->status;

            // Handle stock reversal if cancelling a sale
            if ($oldStatus === 'completed' && $newStatus === 'cancelled') {
                foreach ($sale->items as $item) {
                    $product = $item->product;
                    $product->increment('stock_quantity', $item->quantity);

                    // Create reversal stock history
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity, // Positive for reversal
                        'type' => 'adjustment',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => "Sale cancelled: {$sale->reference_number}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $sale->update([
                'status' => $newStatus,
                'notes' => $sale->notes . "\nStatus changed: {$oldStatus} -> {$newStatus}",
            ]);

            DB::commit();

            return $this->sendUpdated($sale, 'Sale status updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating sale status: ' . $e->getMessage());
        }
    }
}
