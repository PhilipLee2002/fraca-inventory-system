<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends BaseController
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::query();

            // Apply filters
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $suppliers = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($suppliers, 'Suppliers retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving suppliers: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            DB::beginTransaction();

            $supplier = Supplier::create($request->validated());

            DB::commit();

            return $this->sendCreated($supplier, 'Supplier created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier)
    {
        try {
            $supplier->load(['products', 'purchases' => function($query) {
                $query->latest()->limit(10);
            }]);

            return $this->sendSuccess($supplier, 'Supplier retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving supplier: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        try {
            DB::beginTransaction();

            $supplier->update($request->validated());

            DB::commit();

            return $this->sendUpdated($supplier, 'Supplier updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            DB::beginTransaction();

            // Check if supplier has transactions
            $hasTransactions = $supplier->purchases()->exists() || 
                              $supplier->products()->exists();

            if ($hasTransactions) {
                return $this->sendError(
                    'Cannot delete supplier with existing transactions or products. Consider deactivating it instead.',
                    [],
                    422
                );
            }

            $supplier->delete();

            DB::commit();

            return $this->sendDeleted('Supplier deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error deleting supplier: ' . $e->getMessage());
        }
    }
}
