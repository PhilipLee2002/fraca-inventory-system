<?php
// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    // Display all customers
    public function index()
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        $customers = Customer::withCount(['sales' => function($query) {
            $query->where('status', 'completed');
        }])
        ->withSum(['sales' => function($query) {
            $query->where('status', 'completed');
        }], 'total_amount')
        ->latest()
        ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    // Show create form
    public function create()
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('customers.create');
    }

    // Store new customer
    public function store(Request $request)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:retail,wholesale,business,online',
            'tax_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer added successfully.');
    }

    // Show single customer with purchase history
    public function show(Customer $customer)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        // Load sales with their items
        $sales = $customer->sales()
            ->with(['items.product'])
            ->latest()
            ->paginate(15);

        // Calculate summary statistics
        $totalPurchases = $customer->sales()->where('status', 'completed')->count();
        $totalSpent = $customer->sales()->where('status', 'completed')->sum('total_amount');
        $lastPurchase = $customer->sales()->latest()->first();

        // Calculate average order value
        $averageOrderValue = $totalPurchases > 0 ? $totalSpent / $totalPurchases : 0;

        // Get favorite products (most purchased)
        $favoriteProducts = $customer->sales()
            ->selectRaw('products.name, products.sku, SUM(sale_items.quantity) as total_quantity')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        return view('customers.show', compact(
            'customer', 
            'sales', 
            'totalPurchases', 
            'totalSpent', 
            'lastPurchase',
            'averageOrderValue',
            'favoriteProducts'
        ));
    }

    // Show edit form
    public function edit(Customer $customer)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('customers.edit', compact('customer'));
    }

    // Update customer
    public function update(Request $request, Customer $customer)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:retail,wholesale,business,online',
            'tax_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'contact_person' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    // Delete customer (with checks)
    public function destroy(Customer $customer)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if customer has sales
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with existing sales. Consider deactivating instead.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    // Toggle customer active status
    public function toggleStatus(Customer $customer)
    {
        if (!Gate::allows('manage-customers')) {
            abort(403, 'Unauthorized action.');
        }

        $customer->update([
            'is_active' => !$customer->is_active
        ]);

        $status = $customer->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Customer {$status} successfully.");
    }

    // Search customers for autocomplete (for sales form)
    public function search(Request $request)
    {
        $search = $request->input('search');
        
        $customers = Customer::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%")
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'customer_type', 'discount_rate']);

        return response()->json($customers);
    }

    // Get customer's outstanding balance (for credit sales)
    public function getBalance(Customer $customer)
    {
        // Calculate total credit sales minus payments
        $totalCredit = $customer->sales()
            ->where('payment_method', 'credit')
            ->where('status', 'completed')
            ->sum('total_amount');

        $totalPaid = $customer->payments()->sum('amount');

        $outstanding = $totalCredit - $totalPaid;
        $creditLimit = $customer->credit_limit ?? 0;
        $availableCredit = $creditLimit - $outstanding;

        return response()->json([
            'outstanding_balance' => $outstanding,
            'credit_limit' => $creditLimit,
            'available_credit' => $availableCredit,
            'credit_utilization' => $creditLimit > 0 ? ($outstanding / $creditLimit) * 100 : 0
        ]);
    }
}