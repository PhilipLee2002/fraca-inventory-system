<?php
// app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SupplierController extends Controller
{
    // Display all suppliers
    public function index()
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        $suppliers = Supplier::withCount(['purchases' => function($query) {
            $query->where('status', 'completed');
        }])
        ->withSum(['purchases' => function($query) {
            $query->where('status', 'completed');
        }], 'total_amount')
        ->latest()
        ->paginate(20);

        return view('suppliers.index', compact('suppliers'));
    }

    // Show create form
    public function create()
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('suppliers.create');
    }

    // Store new supplier
    public function store(Request $request)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email',
            'notes' => 'nullable|string'
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added successfully.');
    }

    // Show single supplier with purchase history
    public function show(Supplier $supplier)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        // Load purchases with their items
        $purchases = $supplier->purchases()
            ->with(['items.product'])
            ->latest()
            ->paginate(15);

        // Calculate summary statistics
        $totalPurchases = $supplier->purchases()->where('status', 'completed')->count();
        $totalSpent = $supplier->purchases()->where('status', 'completed')->sum('total_amount');
        $lastPurchase = $supplier->purchases()->latest()->first();

        return view('suppliers.show', compact('supplier', 'purchases', 'totalPurchases', 'totalSpent', 'lastPurchase'));
    }

    // Show edit form
    public function edit(Supplier $supplier)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('suppliers.edit', compact('supplier'));
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:20',
            'contact_person_email' => 'nullable|email',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    // Delete supplier (with checks)
    public function destroy(Supplier $supplier)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if supplier has purchases
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier with existing purchases. Consider deactivating instead.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    // Toggle supplier active status
    public function toggleStatus(Supplier $supplier)
    {
        if (!Gate::allows('manage-suppliers')) {
            abort(403, 'Unauthorized action.');
        }

        $supplier->update([
            'is_active' => !$supplier->is_active
        ]);

        $status = $supplier->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Supplier {$status} successfully.");
    }

    // Get supplier products (for purchase form)
    public function getProducts(Supplier $supplier)
    {
        $products = $supplier->products()
            ->where('is_active', true)
            ->select('id', 'name', 'sku', 'current_stock', 'cost_price')
            ->get();

        return response()->json($products);
    }
}