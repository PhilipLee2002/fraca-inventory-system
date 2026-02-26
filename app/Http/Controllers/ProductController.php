<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    // GET /api/products - List all products (API)
    public function index(Request $request)
    {
        if (!Gate::allows('view-products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = Product::with(['category', 'supplier']);

        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('sku', 'LIKE', "%{$request->search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock': $query->where('current_stock', '>', 0); break;
                case 'out_of_stock': $query->where('current_stock', '=', 0); break;
                case 'low_stock': $query->whereColumn('current_stock', '<=', 'reorder_level'); break;
            }
        }

        $products = $query->latest()->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    // POST /api/products - Create product (API)
    public function store(Request $request)
    {
        if (!Gate::allows('create-products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:50',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'initial_stock' => 'required|integer|min:0'
        ]);

        $product = Product::create($validated);

        // Update stock with initial stock
        if ($product->initial_stock > 0) {
            $product->update(['current_stock' => $product->initial_stock]);
            $product->logStockMovement('initial', $product->initial_stock, 'Initial stock');
        }

        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'supplier']),
            'message' => 'Product created successfully'
        ], 201);
    }

    // GET /api/products/{id} - Show product (API)
    public function show(Product $product)
    {
        if (!Gate::allows('view-products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->load(['category', 'supplier', 'stockMovements']);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product retrieved successfully'
        ]);
    }

    // PUT/PATCH /api/products/{id} - Update product (API)
    public function update(Request $request, Product $product)
    {
        if (!Gate::allows('edit-products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0'
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'supplier']),
            'message' => 'Product updated successfully'
        ]);
    }

    // DELETE /api/products/{id} - Delete product (API)
    public function destroy(Product $product)
    {
        if (!Gate::allows('delete-products')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if product has transactions
        if ($product->sales()->exists() || $product->purchases()->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot delete product with existing transactions'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    // GET /api/products/search - Search products (API)
    public function search(Request $request)
    {
        $search = $request->input('search', '');
        
        $products = Product::where('name', 'LIKE', "%{$search}%")
            ->orWhere('sku', 'LIKE', "%{$search}%")
            ->where('is_active', true)
            ->select('id', 'name', 'sku', 'current_stock', 'selling_price')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Search results'
        ]);
    }
}