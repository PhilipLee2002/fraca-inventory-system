<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'supplier']);

            // Apply filters
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $products = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($products, 'Products retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving products: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = Product::create($request->validated());

            DB::commit();

            $product->load(['category', 'supplier']);
            return $this->sendCreated($product, 'Product created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating product: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        try {
            $product->load(['category', 'supplier', 'stockHistories' => function($query) {
                $query->latest()->limit(20);
            }]);

            return $this->sendSuccess($product, 'Product retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving product: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $product->update($request->validated());

            DB::commit();

            $product->load(['category', 'supplier']);
            return $this->sendUpdated($product, 'Product updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating product: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Check if product has transactions
            $hasTransactions = $product->purchaseItems()->exists() || 
                              $product->saleItems()->exists() ||
                              $product->stockHistories()->exists();

            if ($hasTransactions) {
                return $this->sendError(
                    'Cannot delete product with existing transactions. Consider deactivating it instead.',
                    [],
                    422
                );
            }

            $product->delete();

            DB::commit();

            return $this->sendDeleted('Product deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Get low stock products.
     */
    public function lowStock(Request $request)
    {
        try {
            $query = Product::with(['category', 'supplier'])
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->where('is_active', true);

            $products = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($products, 'Low stock products retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving low stock products: ' . $e->getMessage());
        }
    }
}
