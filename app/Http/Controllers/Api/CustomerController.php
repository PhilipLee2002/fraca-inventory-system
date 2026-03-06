<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends BaseController
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::query();

            // Apply filters
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $customers = $query->paginate($request->get('per_page', 20));

            return $this->sendPaginated($customers, 'Customers retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving customers: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::create($request->validated());

            DB::commit();

            return $this->sendCreated($customer, 'Customer created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        try {
            $customer->load(['sales' => function($query) {
                $query->latest()->limit(10);
            }]);

            return $this->sendSuccess($customer, 'Customer retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving customer: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        try {
            DB::beginTransaction();

            $customer->update($request->validated());

            DB::commit();

            return $this->sendUpdated($customer, 'Customer updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        try {
            DB::beginTransaction();

            // Check if customer has transactions
            $hasTransactions = $customer->sales()->exists();

            if ($hasTransactions) {
                return $this->sendError(
                    'Cannot delete customer with existing sales. Consider deactivating it instead.',
                    [],
                    422
                );
            }

            $customer->delete();

            DB::commit();

            return $this->sendDeleted('Customer deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error deleting customer: ' . $e->getMessage());
        }
    }
}
