<?php
// app/Http/Controllers/StockController.php
// stock adjustment endpoint

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:addition,deduction,correction',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string'
        ]);

        \DB::beginTransaction();

        try {
            $product = Product::find($validated['product_id']);
            $old_stock = $product->current_stock;

            // Calculate new stock based on adjustment type
            switch ($validated['adjustment_type']) {
                case 'addition':
                    $new_stock = $old_stock + $validated['quantity'];
                    break;
                case 'deduction':
                    if ($old_stock < $validated['quantity']) {
                        throw new \Exception("Cannot deduct more than available stock. Available: {$old_stock}");
                    }
                    $new_stock = $old_stock - $validated['quantity'];
                    break;
                case 'correction':
                    $new_stock = $validated['quantity'];
                    break;
            }

            // Record stock adjustment
            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'old_stock' => $old_stock,
                'new_stock' => $new_stock,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity_changed' => abs($new_stock - $old_stock),
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'adjusted_by' => auth()->id()
            ]);

            // Update product stock
            $product->update(['current_stock' => $new_stock]);

            // Log stock movement
            $change = $new_stock - $old_stock;
            $product->logStockMovement('adjustment', $change, 
                "Stock adjustment: {$validated['reason']}");

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'adjustment' => $adjustment,
                'product' => $product->fresh()
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}