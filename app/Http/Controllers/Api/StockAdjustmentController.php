<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends BaseController
{
    /**
     * Store a stock adjustment.
     * Accepts: { product_id, quantity_change (signed int), reason }
     */
    public function store(StockAdjustmentRequest $request)
    {
        try {
            DB::beginTransaction();

            $product        = Product::findOrFail($request->product_id);
            $quantityChange = (int) $request->quantity_change;
            $oldStock       = $product->current_stock;
            $newStock       = $oldStock + $quantityChange;

            // Update stock
            $product->update(['current_stock' => $newStock]);

            // Log to stock_histories
            $history = StockHistory::create([
                'product_id'        => $product->id,
                'transaction_type'  => 'adjustment',
                'quantity_change'   => $quantityChange,
                'previous_quantity' => $oldStock,
                'new_quantity'      => $newStock,
                'notes'             => $request->reason,
            ]);

            DB::commit();

            $product->load('category', 'supplier');

            // Real-time stock alert after adjustment
            if ($product->current_stock === 0) {
                Alert::createForProduct($product, 'out_of_stock');
            } elseif ($product->current_stock <= $product->reorder_level) {
                Alert::createForProduct($product, 'low_stock');
            }

            return $this->sendCreated([
                'product'         => $product,
                'history'         => $history,
                'quantity_change' => $quantityChange,
                'old_stock'       => $oldStock,
                'new_stock'       => $newStock,
            ], 'Stock adjusted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error adjusting stock: ' . $e->getMessage());
        }
    }

    /**
     * List stock adjustment history (transaction_type = adjustment).
     */
    public function index(Request $request)
    {
        try {
            $query = StockHistory::with('product:id,name,sku')
                ->where('transaction_type', 'adjustment');

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('start_date')) {
                $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
            }

            if ($request->filled('end_date')) {
                $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
            }

            $adjustments = $query->latest()->paginate($request->get('per_page', 20));

            return $this->sendPaginated($adjustments, 'Stock adjustments retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving stock adjustments: ' . $e->getMessage());
        }
    }
}
