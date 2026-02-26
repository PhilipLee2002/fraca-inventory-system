<?php
// app/Http/Controllers/PurchaseController.php
// purchase creation endpoint

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // GET /api/purchases
    public function index(Request $request)
    {
        if (!Gate::allows('view-purchases')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Purchase::with(['supplier', 'items.product']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->latest()->paginate($request->input('per_page', 25));

        return response()->json(['success' => true, 'data' => $purchases]);
    }

    // GET /api/purchases/{id}
    public function show(Purchase $purchase)
    {
        if (!Gate::allows('view-purchases')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $purchase->load(['items.product', 'supplier']);

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    // POST /api/purchases
    public function store(Request $request)
    {
        if (!Gate::allows('create-purchases')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|unique:purchases',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $total_amount = 0;
            foreach ($validated['items'] as $item) {
                $total_amount += $item['quantity'] * $item['unit_cost'];
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'invoice_number' => $validated['invoice_number'],
                'total_amount' => $total_amount,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed'
            ]);

            foreach ($validated['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost']
                ]);

                $product = Product::find($item['product_id']);
                $product->increment('current_stock', $item['quantity']);
                $product->logStockMovement('purchase', $item['quantity'], "Purchase #{$purchase->invoice_number}");
            }

            DB::commit();

            return response()->json(['success' => true, 'data' => $purchase->load('items.product')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}