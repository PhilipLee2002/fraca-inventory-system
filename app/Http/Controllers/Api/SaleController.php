<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Alert;
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

            $items         = $request->input('items');
            $customerId    = $request->input('customer_id') ?: null;
            $saleDate      = $request->input('sale_date');
            $paymentMethod = $request->input('payment_method', 'cash');
            $notes         = $request->input('notes');
            $status        = $request->input('status', 'pending');

            // VALIDATION: Check stock FIRST
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->current_stock < $item['quantity']) {
                    throw new \Exception(
                        "Insufficient stock for product: " . ($product->name ?? 'Unknown')
                    );
                }
            }

            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Create sale
            $sale = Sale::create([
                'customer_id' => $customerId ?: null,
                'user_id' => auth()->id(),
                'invoice_number' => 'INV-' . date('YmdHis'),
                'sale_date' => $saleDate,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'notes' => $notes,
            ]);

            // Create sale items and update stock
            foreach ($items as $item) {
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
                $previousStock = $product->current_stock;
                $product->decrement('current_stock', $item['quantity']);
                $product->refresh();

                // Create stock history record
                StockHistory::create([
                    'product_id'        => $item['product_id'],
                    'quantity_change'   => -$item['quantity'],
                    'previous_quantity' => $previousStock,
                    'new_quantity'      => $previousStock - $item['quantity'],
                    'transaction_type'  => 'sale',
                    'reference_id'      => $sale->id,
                    'reference_type'    => Sale::class,
                    'notes'             => "Sale: {$sale->invoice_number}",
                ]);

                // Real-time stock alert
                if ($product->current_stock === 0) {
                    Alert::createForProduct($product, 'out_of_stock');
                } elseif ($product->current_stock <= $product->reorder_level) {
                    Alert::createForProduct($product, 'low_stock');
                }
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
     * Update an existing sale (replaces items, recalculates total, adjusts stock).
     */
    public function update(Request $request, Sale $sale)
    {
        try {
            $request->validate([
                'customer_id'        => 'nullable|exists:customers,id',
                'sale_date'          => 'required|date',
                'payment_method'     => 'nullable|in:cash,card,transfer',
                'status'             => 'required|in:pending,completed,cancelled',
                'notes'              => 'nullable|string',
                'items'              => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity'   => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            // Reverse stock for old items
            foreach ($sale->items as $oldItem) {
                $oldItem->product->increment('current_stock', $oldItem->quantity);
            }
            $sale->items()->delete();

            $items = $request->input('items');

            // Check stock for new items
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || $product->current_stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for: " . ($product->name ?? 'Unknown'));
                }
            }

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total'      => $item['quantity'] * $item['unit_price'],
                ]);

                $product = Product::find($item['product_id']);
                $previousStock = $product->current_stock;
                $product->decrement('current_stock', $item['quantity']);
                $product->refresh();

                StockHistory::create([
                    'product_id'        => $item['product_id'],
                    'quantity_change'   => -$item['quantity'],
                    'previous_quantity' => $previousStock,
                    'new_quantity'      => $previousStock - $item['quantity'],
                    'transaction_type'  => 'sale',
                    'reference_id'      => $sale->id,
                    'reference_type'    => Sale::class,
                    'notes'             => "Sale updated: {$sale->invoice_number}",
                ]);

                if ($product->current_stock === 0) {
                    Alert::createForProduct($product, 'out_of_stock');
                } elseif ($product->current_stock <= $product->reorder_level) {
                    Alert::createForProduct($product, 'low_stock');
                }
            }

            $sale->update([
                'customer_id'    => $request->input('customer_id') ?: null,
                'sale_date'      => $request->input('sale_date'),
                'payment_method' => $request->input('payment_method', 'cash'),
                'status'         => $request->input('status'),
                'notes'          => $request->input('notes'),
                'total_amount'   => $totalAmount,
            ]);

            DB::commit();

            $sale->load(['customer', 'items.product']);
            return $this->sendUpdated($sale, 'Sale updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating sale: ' . $e->getMessage());
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
                'user:id,name',
            ]);

            return $this->sendSuccess($sale, 'Sale retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving sale: ' . $e->getMessage());
        }
    }

    /**
     * Delete a sale.
     */
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();
            // Reverse stock for each item
            foreach ($sale->items as $item) {
                $previousStock = $item->product->current_stock;
                $item->product->increment('current_stock', $item->quantity);
                StockHistory::create([
                    'product_id'        => $item->product_id,
                    'quantity_change'   => $item->quantity,
                    'previous_quantity' => $previousStock,
                    'new_quantity'      => $previousStock + $item->quantity,
                    'transaction_type'  => 'adjustment',
                    'reference_id'      => $sale->id,
                    'reference_type'    => Sale::class,
                    'notes'             => "Sale deleted: {$sale->invoice_number}",
                ]);
            }
            $sale->items()->delete();
            $sale->delete();
            DB::commit();
            return $this->sendDeleted('Sale deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error deleting sale: ' . $e->getMessage());
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
                        'notes' => "Sale cancelled: {$sale->invoice_number}",
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

