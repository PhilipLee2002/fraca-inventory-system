<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends BaseController
{
    /**
     * Store a newly created stock adjustment.
     */
    public function store(StockAdjustmentRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);

            // Calculate new quantity based on adjustment type
            $quantityChange = $request->quantity;
            
            if ($request->adjustment_type === 'subtraction') {
                $quantityChange = -$quantityChange;
            } elseif ($request->adjustment_type === 'correction') {
                // For correction, we set stock to specific quantity
                $quantityChange = $request->quantity - $product->stock_quantity;
            }

            // Update product stock
            $product->increment('stock_quantity', $quantityChange);

            // Create stock history record
            $stockHistory = StockHistory::create([
                'product_id' => $product->id,
                'quantity' => $quantityChange,
                'type' => 'adjustment',
                'adjustment_type' => $request->adjustment_type,
                'reason' => $request->reason,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Record old and new stock quantities
            $stockHistory->metadata = [
                'old_stock' => $product->stock_quantity - $quantityChange,
                'new_stock' => $product->stock_quantity,
                'adjustment_type' => $request->adjustment_type,
            ];
            $stockHistory->save();

            DB::commit();

            return $this->sendCreated([
                'product' => $product,
                'adjustment' => $stockHistory,
                'quantity_change' => $quantityChange,
            ], 'Stock adjusted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error adjusting stock: ' . $e->getMessage());
        }
    }

    /**
     * Get stock adjustment history.
     */
    public function index(Request $request)
    {
        try {
            $query = StockHistory::with(['product', 'createdBy'])
                ->where('type', 'adjustment');

            // Apply filters
            if ($request->has('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('adjustment_type')) {
                $query->where('adjustment_type', $request->adjustment_type);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            // Apply sorting
            $query->orderBy('created_at', 'desc');

            $adjustments = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($adjustments, 'Stock adjustments retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving stock adjustments: ' . $e->getMessage());
        }
    }
}
