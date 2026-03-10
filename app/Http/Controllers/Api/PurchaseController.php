<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends BaseController
{
    /**
     * Display a listing of purchases.
     */
    public function index(Request $request)
    {
        try {
            $query = Purchase::with(['supplier', 'items.product']);

            // Apply filters
            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('purchase_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            // Apply sorting
            $query->orderBy('purchase_date', 'desc')
                  ->orderBy('created_at', 'desc');

            $purchases = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($purchases, 'Purchases retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving purchases: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created purchase.
     */
    public function store(StorePurchaseRequest $request)
    {
        try {
            DB::beginTransaction();

            // Get input data using input() method
            $items = $request->input('items');
            $supplierId = $request->input('supplier_id');
            $purchaseDate = $request->input('purchase_date');
            $notes = $request->input('notes');
            $status = $request->input('status', 'pending');

            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'user_id' => auth()->id(),
                'purchase_number' => 'PUR-' . date('YmdHis'),
                'purchase_date' => $purchaseDate,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'status' => $status,
            ]);

            // Create purchase items and update stock
            foreach ($items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->increment('current_stock', $item['quantity']);

                // Create stock history record
                StockHistory::create([
                    'product_id' => $item['product_id'],
                    'quantity_change' => $item['quantity'],
                    'transaction_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'notes' => "Purchase: {$purchase->purchase_number}",
                ]);
            }

            DB::commit();

            $purchase->load(['supplier', 'items.product']);

            return $this->sendCreated($purchase, 'Purchase created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        try {
            $purchase->load([
                'supplier',
                'items.product',
                'createdBy'
            ]);

            return $this->sendSuccess($purchase, 'Purchase retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving purchase: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase status.
     */
    public function updateStatus(Request $request, Purchase $purchase)
    {
        try {
            $request->validate([
                'status' => 'required|in:received,partially_received,cancelled',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $oldStatus = $purchase->status;
            $newStatus = $request->status;

            // Handle stock reversal if cancelling a received purchase
            if ($oldStatus === 'received' && $newStatus === 'cancelled') {
                foreach ($purchase->items as $item) {
                    $product = $item->product;
                    $product->decrement('stock_quantity', $item->quantity);

                    // Create reversal stock history
                    StockHistory::create([
                        'product_id' => $item->product_id,
                        'quantity' => -$item->quantity,
                        'type' => 'adjustment',
                        'reference_type' => Purchase::class,
                        'reference_id' => $purchase->id,
                        'notes' => "Purchase cancelled: {$purchase->purchase_number}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $purchase->update([
                'status' => $newStatus,
                'notes' => $purchase->notes . "\nStatus changed: {$oldStatus} -> {$newStatus}",
            ]);

            DB::commit();

            return $this->sendUpdated($purchase, 'Purchase status updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating purchase status: ' . $e->getMessage());
        }
    }
}

