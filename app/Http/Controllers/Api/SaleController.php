<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SaleStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends BaseController
{
    public function __construct(private SaleStockService $stock)
    {
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        try {
            $query = Sale::with(['customer', 'items.product']);

            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $from = $request->input('start_date', $request->input('date_from'));
            $to = $request->input('end_date', $request->input('date_to'));
            if ($from) {
                $query->whereDate('sale_date', '>=', $from);
            }
            if ($to) {
                $query->whereDate('sale_date', '<=', $to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            }

            $query->orderBy('sale_date', 'desc')
                ->orderBy('created_at', 'desc');

            $sales = $query->paginate($request->get('per_page', 20));
            $this->hideNestedSaleCosts($sales);

            return $this->sendPaginated($sales, 'Sales retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving sales: '.$e->getMessage());
        }
    }

    /**
     * Store a newly created sale.
     */
    public function store(StoreSaleRequest $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->input('items');
            $status = $request->input('status', 'pending');
            $completed = $this->stock->isCompleted($status);

            if ($completed) {
                $this->stock->assertAvailable($items);
            }

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }

            $sale = Sale::create([
                'customer_id' => $request->input('customer_id') ?: null,
                'user_id' => auth()->id(),
                'invoice_number' => 'INV-'.date('YmdHis'),
                'sale_date' => $request->input('sale_date'),
                'total_amount' => $totalAmount,
                'payment_method' => $request->input('payment_method', 'cash'),
                'reference_number' => $request->input('reference_number'),
                'status' => $status,
                'payment_status' => $completed ? 'paid' : 'pending',
                'notes' => $request->input('notes'),
            ]);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            if ($completed) {
                $this->stock->decrementFor($sale);
            }

            DB::commit();

            $sale->load(['customer', 'items.product']);
            $this->hideNestedSaleCosts($sale);

            return $this->sendCreated($sale, 'Sale created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating sale: '.$e->getMessage());
        }
    }

    /**
     * Update an existing sale (replaces items, recalculates total, adjusts stock).
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        try {
            DB::beginTransaction();

            $wasCompleted = $this->stock->isCompleted($sale->status);
            if ($wasCompleted) {
                $this->stock->incrementFor($sale, "Sale updated (stock restored): {$sale->invoice_number}");
            }

            $sale->items()->delete();

            $items = $request->input('items');
            $status = $request->input('status');
            $nowCompleted = $this->stock->isCompleted($status);

            if ($nowCompleted) {
                $this->stock->assertAvailable($items);
            }

            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_price'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $sale->update([
                'customer_id' => $request->input('customer_id') ?: null,
                'sale_date' => $request->input('sale_date'),
                'payment_method' => $request->input('payment_method', 'cash'),
                'reference_number' => $request->input('reference_number'),
                'status' => $status,
                'payment_status' => $nowCompleted ? 'paid' : 'pending',
                'notes' => $request->input('notes'),
                'total_amount' => $totalAmount,
            ]);

            if ($nowCompleted) {
                $this->stock->decrementFor($sale->fresh('items.product'));
            }

            DB::commit();

            $sale->load(['customer', 'items.product']);
            $this->hideNestedSaleCosts($sale);

            return $this->sendUpdated($sale, 'Sale updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating sale: '.$e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        try {
            $sale->load([
                'customer',
                'items.product',
                'user:id,name',
            ]);
            $this->hideNestedSaleCosts($sale);

            return $this->sendSuccess($sale, 'Sale retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving sale: '.$e->getMessage());
        }
    }

    /**
     * Delete a sale.
     */
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            if ($this->stock->isCompleted($sale->status)) {
                $this->stock->incrementFor($sale, "Sale deleted: {$sale->invoice_number}");
            }

            $sale->items()->delete();
            $sale->delete();
            DB::commit();

            return $this->sendDeleted('Sale deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error deleting sale: '.$e->getMessage());
        }
    }

    /**
     * Update sale status.
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        try {
            $request->validate([
                'status' => 'required|in:completed,cancelled,pending',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $oldStatus = $sale->status;
            $newStatus = $request->status;

            $this->stock->syncStatusChange($sale, $oldStatus, $newStatus);

            $sale->update([
                'status' => $newStatus,
                'payment_status' => $this->stock->isCompleted($newStatus) ? 'paid' : $sale->payment_status,
                'notes' => trim(($sale->notes ? $sale->notes."\n" : '')."Status changed: {$oldStatus} -> {$newStatus}"),
            ]);

            DB::commit();

            return $this->sendUpdated($sale->fresh(['customer', 'items.product']), 'Sale status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error updating sale status: '.$e->getMessage());
        }
    }
}
