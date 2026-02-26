<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * StockAdjustmentController
 * 
 * Handles manual stock adjustments for products.
 * Supports additions, subtractions, and corrections to inventory.
 */
class StockAdjustmentController extends Controller
{
    /**
     * Store a new stock adjustment.
     * 
     * Creates a stock adjustment record and updates the product's stock quantity.
     * Wraps the operation in a database transaction for data consistency.
     *
     * @param  \App\Http\Requests\StockAdjustmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StockAdjustmentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($validated['product_id']);
            $oldStock = $product->stock_quantity;

            // Calculate new stock based on adjustment type
            $newStock = match ($validated['adjustment_type']) {
                'addition' => $oldStock + $validated['quantity'],
                'subtraction' => $oldStock - $validated['quantity'],
                'correction' => $validated['quantity'],
                default => $oldStock,
            };

            // Update product stock
            $product->update(['stock_quantity' => $newStock]);

            // Record the adjustment
            $adjustment = StockAdjustment::create([
                'product_id' => $validated['product_id'],
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => $validated['quantity'],
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $validated['reason'],
                'reference' => $validated['reference'] ?? null,
                'adjusted_date' => $validated['adjusted_date'],
                'notes' => $validated['notes'] ?? null,
                'adjusted_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Stock adjustment recorded successfully',
                'data' => [
                    'adjustment' => $adjustment,
                    'product' => $product->fresh(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to record stock adjustment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
