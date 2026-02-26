# Refactoring Implementation - Specific Code Changes
## File-by-File Code Modifications

---

## 1. MIGRATION FILE

**Location:** `database/migrations/2026_02_17_000001_simplify_schema_consolidate_tables.php`

See **REFACTORING_PLAN.md Part 3** for full migration code.

**Key Points:**
- Migrates stock_adjustments data to stock_history
- Drops unused tables
- Removes extra columns
- Safe: Uses Schema::hasColumn/hasTable checks

---

## 2. MODEL CHANGES

### 2.1 Product.php

**BEFORE:**
```php
protected $fillable = [
    'name', 'sku', 'barcode', 'description', 'cost_price', 'selling_price',
    'current_stock', 'initial_stock', 'reorder_level', 'minimum_stock',
    'unit_of_measurement', 'category_id', 'supplier_id', 'image', 'is_active',
];
```

**AFTER:**
```php
protected $fillable = [
    'name', 'sku', 'description', 'cost_price', 'selling_price',
    'current_stock', 'reorder_level', 'category_id', 'supplier_id', 'is_active',
];
```

**PHPDoc Changes:**
```php
// REMOVE these @property lines:
* @property string|null $barcode
* @property int|null $initial_stock
* @property int|null $minimum_stock
* @property string|null $unit_of_measurement
* @property string|null $image
```

**Relationships:** No changes needed (they reference by foreign key, not column)

---

### 2.2 Purchase.php

**BEFORE:**
```php
protected $fillable = [
    'supplier_id', 'user_id', 'purchase_number', 'reference_number',
    'invoice_number', 'total_amount', 'shipping_cost', 'tax_amount',
    'discount_amount', 'payment_method', 'notes', 'status', 'purchase_date',
    'delivery_date', 'created_by',
];
```

**AFTER:**
```php
protected $fillable = [
    'supplier_id', 'user_id', 'purchase_number', 'total_amount',
    'notes', 'status', 'purchase_date',
];
```

**PHPDoc Changes:**
```php
// REMOVE these @property lines:
* @property string|null $reference_number
* @property string|null $invoice_number
* @property \Carbon\Carbon|null $delivery_date
* @property float $shipping_cost
* @property float $tax_amount
* @property float $discount_amount
* @property string|null $payment_method
* @property int|null $created_by
```

**Updated PHPDoc:**
```php
/**
 * App\Models\Purchase
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $user_id
 * @property string $purchase_number
 * @property float $total_amount
 * @property string|null $notes
 * @property string $status
 * @property \Carbon\Carbon|null $purchase_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Supplier $supplier
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\PurchaseItem> $items
 */
```

**Relationships:** No changes

---

### 2.3 PurchaseItem.php

**BEFORE:**
```php
protected $fillable = [
    'purchase_id', 'product_id', 'quantity', 'unit_price',
    'tax_rate', 'discount', 'total',
];
```

**AFTER:**
```php
protected $fillable = [
    'purchase_id', 'product_id', 'quantity', 'unit_price', 'total',
];
```

**PHPDoc Changes:**
```php
// REMOVE:
* @property float $tax_rate
* @property float $discount

// KEEP:
* @property int $id
* @property int $purchase_id
* @property int $product_id
* @property int $quantity
* @property float $unit_price
* @property float $total
* @property \Carbon\Carbon $created_at
* @property \Carbon\Carbon $updated_at
```

---

### 2.4 Sale.php

**BEFORE:**
```php
protected $fillable = [
    'customer_id', 'user_id', 'invoice_number', 'reference_number',
    'total_amount', 'shipping_cost', 'tax_amount', 'discount_amount',
    'payment_method', 'payment_status', 'notes', 'status', 'sale_date',
    'due_date', 'created_by',
];
```

**AFTER:**
```php
protected $fillable = [
    'customer_id', 'user_id', 'invoice_number', 'total_amount',
    'payment_method', 'notes', 'status', 'sale_date',
];
```

**PHPDoc Changes:**
```php
// REMOVE:
* @property string|null $reference_number
* @property \Carbon\Carbon|null $due_date
* @property float $shipping_cost
* @property float $tax_amount
* @property float $discount_amount
* @property string $payment_status
* @property int|null $created_by

// KEEP basic fields, keep payment_method for simplicity
```

---

### 2.5 SaleItem.php

**BEFORE:**
```php
protected $fillable = [
    'sale_id', 'product_id', 'quantity', 'unit_price',
    'tax_rate', 'discount', 'total',
];
```

**AFTER:**
```php
protected $fillable = [
    'sale_id', 'product_id', 'quantity', 'unit_price', 'total',
];
```

**Same changes as PurchaseItem.php - remove tax_rate, discount**

---

### 2.6 StockHistory.php

**BEFORE:**
```php
public function historyable()
{
    return $this->morphTo();
}

// @property text for reference_type, reference_id_type
```

**AFTER:**
```php
/**
 * Simple reference relationship based on transaction_type
 */
public function purchase()
{
    return $this->belongsTo(Purchase::class, 'reference_id')
        ->where('transaction_type', 'purchase');
}

public function sale()
{
    return $this->belongsTo(Sale::class, 'reference_id')
        ->where('transaction_type', 'sale');
}

// PHPDoc
/**
 * @property int $id
 * @property int $product_id
 * @property int $quantity_change
 * @property string $transaction_type (purchase, sale, adjustment)
 * @property int|null $reference_id
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
```

**$fillable:**
```php
protected $fillable = [
    'product_id', 'quantity_change', 'transaction_type', 'reference_id', 'notes',
];
```

---

### 2.7 Role.php

**BEFORE:**
```php
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'role_permission');
}

public function hasPermission($permission)
{
    // Check if user's role has this permission
}
```

**AFTER:**
```php
protected $casts = [
    'permissions' => 'array',
];

// In PHPDoc:
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array|null $permissions (JSON, e.g., ['view_products', 'edit_sales'])
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\User> $users
 */

public function hasPermission($permission)
{
    if (!$this->permissions) {
        return false;
    }
    return in_array($permission, $this->permissions);
}
```

**$fillable:**
```php
protected $fillable = ['name', 'description', 'permissions'];
```

---

### 2.8 DELETE Permission.php

**Action:** Delete the file entirely
```bash
rm app/Models/Permission.php
```

---

### 2.9 DELETE StockAdjustment.php

**Action:** Delete the file
```bash
rm app/Models/StockAdjustment.php
```

**Alternative:** Keep file but mark as deprecated, integrate into StockHistory instead

---

## 3. REQUEST VALIDATION CHANGES

### 3.1 StoreProductRequest.php

**BEFORE:**
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:products|max:100',
        'barcode' => 'required|string|unique:products|max:100',
        'description' => 'nullable|string',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'unit_of_measurement' => 'nullable|string|max:50',
        'minimum_stock' => 'nullable|integer|min:0',
        'initial_stock' => 'nullable|integer|min:0',
        'reorder_level' => 'nullable|integer|min:0',
        'image' => 'nullable|image|max:2048',
    ];
}
```

**AFTER:**
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'sku' => 'required|string|unique:products|max:100',
        'description' => 'nullable|string',
        'cost_price' => 'required|numeric|min:0',
        'selling_price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'supplier_id' => 'required|exists:suppliers,id',
        'reorder_level' => 'nullable|integer|min:0',
        'is_active' => 'nullable|boolean',
    ];
}
```

---

### 3.2 StorePurchaseRequest.php

**BEFORE:**
```php
public function rules(): array
{
    return [
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_number' => 'nullable|string',
        'reference_number' => 'nullable|string',
        'invoice_number' => 'nullable|string',
        'purchase_date' => 'required|date',
        'delivery_date' => 'nullable|date|after:purchase_date',
        'shipping_cost' => 'nullable|numeric|min:0',
        'tax_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'payment_method' => 'nullable|in:cash,check,transfer',
        'status' => 'required|in:pending,received,cancelled',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        'items.*.discount' => 'nullable|numeric|min:0',
    ];
}
```

**AFTER:**
```php
public function rules(): array
{
    return [
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_date' => 'required|date',
        'status' => 'required|in:pending,received,cancelled',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];
}
```

---

### 3.3 StoreSaleRequest.php

**BEFORE:**
```php
public function rules(): array
{
    return [
        'customer_id' => 'required|exists:customers,id',
        'invoice_number' => 'nullable|string',
        'reference_number' => 'nullable|string',
        'sale_date' => 'required|date',
        'due_date' => 'nullable|date|after:sale_date',
        'shipping_cost' => 'nullable|numeric|min:0',
        'tax_amount' => 'nullable|numeric|min:0',
        'discount_amount' => 'nullable|numeric|min:0',
        'payment_method' => 'nullable|in:cash,card,transfer',
        'payment_status' => 'nullable|in:paid,pending,overdue',
        'status' => 'required|in:pending,completed,cancelled',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        'items.*.discount' => 'nullable|numeric|min:0',
    ];
}
```

**AFTER:**
```php
public function rules(): array
{
    return [
        'customer_id' => 'required|exists:customers,id',
        'invoice_number' => 'nullable|string',
        'sale_date' => 'required|date',
        'payment_method' => 'required|in:cash,card,transfer',
        'status' => 'required|in:pending,completed,cancelled',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Stock validation logic here (in controller, not request)
    });
}
```

---

## 4. CONTROLLER CHANGES

### 4.1 ProductController.php - Key Simplifications

**Remove from index() method:**
```php
// REMOVE THIS:
->where('unit_of_measurement', $request->unit)
->where('minimum_stock', $request->min_stock)
->where('initial_stock', $request->initial)

// Handle barcode scanning:
if ($request->has('barcode')) {
    // Barcode scanning logic - schedule for Phase 5
}

// Image handling in store/update - remove for now
```

**Simplified index():**
```php
public function index(Request $request)
{
    try {
        $query = Product::with(['category', 'supplier'])
            ->when($request->has('category_id'), function($q) {
                $q->where('category_id', $request->category_id);
            })
            ->when($request->has('supplier_id'), function($q) {
                $q->where('supplier_id', $request->supplier_id);
            })
            ->when($request->has('is_active'), function($q) {
                $q->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->has('search'), function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return $this->sendPaginated($query, 'Products retrieved successfully');
    } catch (\Exception $e) {
        return $this->sendError('Error retrieving products: ' . $e->getMessage());
    }
}
```

**Simplified store():**
```php
public function store(StoreProductRequest $request)
{
    try {
        DB::beginTransaction();

        $product = Product::create($request->validated());
        // No image handling

        DB::commit();
        return $this->sendCreated($product, 'Product created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return $this->sendError('Error creating product: ' . $e->getMessage());
    }
}
```

**Remove lowStock() or simplify:**
```php
public function lowStock(Request $request)
{
    try {
        $products = Product::where('current_stock', '<=', DB::raw('reorder_level'))
            ->where('is_active', true)
            ->with(['category', 'supplier'])
            ->paginate($request->get('per_page', 20));

        return $this->sendPaginated($products, 'Low stock products');
    } catch (\Exception $e) {
        return $this->sendError('Error: ' . $e->getMessage());
    }
}
```

---

### 4.2 PurchaseController.php - Major Simplification

**Simplified store() method:**
```php
public function store(StorePurchaseRequest $request)
{
    try {
        DB::beginTransaction();

        // Simple total calculation - no tax/discount/shipping
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $totalAmount += $itemTotal;
        }

        // Create purchase with only essential fields
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'user_id' => auth()->id(),
            'purchase_number' => 'PUR-' . date('YmdHis'),
            'purchase_date' => $request->purchase_date,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'status' => $request->status ?? 'pending',
        ]);

        // Create items and update stock
        foreach ($request->items as $item) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);

            // Update product stock (CRITICAL)
            $product = Product::find($item['product_id']);
            $product->increment('current_stock', $item['quantity']);

            // Log to stock history (NO MORE MORPHS)
            StockHistory::create([
                'product_id' => $product->id,
                'quantity_change' => $item['quantity'],
                'transaction_type' => 'purchase',
                'reference_id' => $purchase->id,
                'notes' => "Purchase: {$purchase->purchase_number}",
            ]);
        }

        DB::commit();

        $purchase->load(['supplier', 'items.product']);
        return $this->sendCreated($purchase, 'Purchase created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return $this->sendError('Error creating purchase: ' . $e->getMessage());
    }
}
```

**Remove:**
- delivery_date handling
- tax/discount/shipping calculations
- reference_number, invoice_number logic
- created_by field
- Complex total calculations

---

### 4.3 SaleController.php - Major Simplification

**Simplified store() method:**
```php
public function store(StoreSaleRequest $request)
{
    try {
        DB::beginTransaction();

        // VALIDATION: Check stock FIRST
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->current_stock < $item['quantity']) {
                throw new \Exception(
                    "Insufficient stock for product: {$product->name ?? 'Unknown'}"
                );
            }
        }

        // Simple total calculation
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        // Create sale
        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'user_id' => auth()->id(),
            'invoice_number' => 'INV-' . date('YmdHis'),
            'sale_date' => $request->sale_date,
            'total_amount' => $totalAmount,
            'payment_method' => $request->payment_method,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
        ]);

        // Create items and update stock
        foreach ($request->items as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);

            // Decrement stock (CRITICAL)
            $product = Product::find($item['product_id']);
            $product->decrement('current_stock', $item['quantity']);

            // Check for low stock alert (optional)
            if ($product->current_stock <= $product->reorder_level) {
                Alert::firstOrCreate(
                    ['product_id' => $product->id, 'alert_type' => 'low_stock'],
                    [
                        'message' => "{$product->name} is below reorder level",
                        'severity' => 'high',
                        'is_resolved' => false,
                    ]
                );
            }

            // Log to stock history
            StockHistory::create([
                'product_id' => $product->id,
                'quantity_change' => -$item['quantity'],
                'transaction_type' => 'sale',
                'reference_id' => $sale->id,
                'notes' => "Sale: {$sale->invoice_number}",
            ]);
        }

        DB::commit();

        $sale->load(['customer', 'items.product']);
        return $this->sendCreated($sale, 'Sale created successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return $this->sendError('Error creating sale: ' . $e->getMessage());
    }
}
```

**Remove:**
- due_date, reference_number
- tax/discount/shipping/payment_status handling
- created_by field
- Complex calculations

---

### 4.4 DELETE StockAdjustmentController or Simplify

**Option A: Create simple endpoint in ProductController**
```php
public function adjustStock(Request $request, Product $product)
{
    $request->validate([
        'quantity_change' => 'required|integer',
        'reason' => 'required|string',
        'notes' => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        $product->increment('current_stock', $request->quantity_change);

        StockHistory::create([
            'product_id' => $product->id,
            'quantity_change' => $request->quantity_change,
            'transaction_type' => 'adjustment',
            'reference_id' => null,
            'notes' => "{$request->reason} - {$request->notes}",
        ]);

        DB::commit();

        return $this->sendSuccess(
            ['product' => $product],
            'Stock adjusted successfully'
        );
    } catch (\Exception $e) {
        DB::rollBack();
        return $this->sendError('Error adjusting stock: ' . $e->getMessage());
    }
}
```

**Option B: Delete StockAdjustmentController entirely**
- Remove file
- Remove route
- Handle adjustments through manual StockHistory entries

---

### 4.5 routes/api.php - Simplification

**BEFORE:**
```php
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
        Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store']);
        // ... reports ...
    });
});
```

**AFTER:**
```php
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
    
    Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
    Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
    
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
});
```

---

## 5. FACTORY FILE UPDATES

### 5.1 ProductFactory.php

**Remove:**
```php
'barcode' => $this->faker->unique()->bothify('####-????'),
'unit_of_measurement' => $this->faker->randomElement(['pcs', 'kg', 'lbs']),
'minimum_stock' => $this->faker->numberBetween(5, 20),
'initial_stock' => $this->faker->numberBetween(50, 200),
'image' => null,
```

---

### 5.2 PurchaseFactory.php

**Remove:**
```php
'reference_number' => $this->faker->uuid(),
'invoice_number' => $this->faker->unique()->bothify('INV-####-??'),
'delivery_date' => $this->faker->dateTime(),
'shipping_cost' => $this->faker->randomFloat(2, 10, 200),
'tax_amount' => $this->faker->randomFloat(2, 10, 300),
'discount_amount' => $this->faker->randomFloat(2, 0, 100),
'payment_method' => $this->faker->randomElement(['cash', 'check', 'transfer']),
'created_by' => User::factory(),
```

---

### 5.3 SaleFactory.php

**Remove:**
```php
'reference_number' => $this->faker->uuid(),
'due_date' => $this->faker->dateTime(),
'shipping_cost' => $this->faker->randomFloat(2, 10, 200),
'tax_amount' => $this->faker->randomFloat(2, 10, 300),
'discount_amount' => $this->faker->randomFloat(2, 0, 100),
'payment_status' => $this->faker->randomElement(['paid', 'pending', 'overdue']),
'created_by' => User::factory(),
```

---

### 5.4 PurchaseItemFactory.php & SaleItemFactory.php

**Remove:**
```php
'tax_rate' => $this->faker->randomElement([5, 10, 15, 20]),
'discount' => $this->faker->randomFloat(2, 0, 50),
```

---

### 5.5 StockHistoryFactory.php

**BEFORE:**
```php
'reference_type' => $this->faker->optional()->randomElement([
    'App\Models\Purchase',
    'App\Models\Sale',
    'App\Models\StockAdjustment'
]),
'reference_id_type' => $this->faker->optional()->randomElement(['purchase', 'sale', 'adjustment']),
```

**AFTER:**
```php
'transaction_type' => $this->faker->randomElement(['purchase', 'sale', 'adjustment']),
'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
```

---

### 5.6 DELETE StockAdjustmentFactory.php

```bash
rm database/factories/StockAdjustmentFactory.php
```

---

## 6. TEST REFACTORING

### 6.1 StockServiceTest.php - From 11 tests to 7

**Tests to KEEP:**
1. test_stock_incremented_after_purchase
2. test_stock_decremented_after_sale
3. test_overselling_prevented
4. test_low_stock_alert_generated
5. test_stock_history_recorded
6. test_manual_adjustment_logged
7. test_stock_never_negative

**Tests to DELETE:**
- test_stock_history_polymorphic_tracking
- test_purchase_transaction_atomicity
- test_no_low_stock_alert_when_stock_sufficient (merge into test 4)
- Test methods related to morphTo relationships

---

### 6.2 PurchaseServiceTest.php - From 10 tests to 4

**Tests to KEEP:**
1. test_purchase_created_with_valid_data
2. test_stock_updated_on_purchase
3. test_purchase_order_number_generated
4. test_purchase_with_multiple_items

**Tests to DELETE:**
- test_purchase_total_calculated_correctly (tax/discount)
- test_purchase_relationships (simplify into keep tests)
- test_purchase_status_transitions
- test_purchase_cancellation_transaction_safety
- test_purchase_transaction_creates_all_records

---

### 6.3 SaleServiceTest.php - From 10 tests to 4

**Tests to KEEP:**
1. test_sale_created_with_valid_data
2. test_stock_decremented_on_sale
3. test_sale_prevented_insufficient_stock
4. test_sale_invoice_generated

**Tests to DELETE:**
- test_sale_total_calculated_correctly
- test_sale_relationships
- test_complete_sales_workflow (too complex)
- test_sale_with_multiple_products

---

## SUMMARY OF CHANGES

| Category | Before | After | Change |
|----------|--------|-------|--------|
| Database Tables | 14 | 10 | -4 tables |
| Model Files | 14 | 12 | -2 deleted |
| Controllers | 8 | 5 | -3 simplified/deleted |
| Request Classes | 5 | 3 | -2 simplified |
| Test Methods | 31 | 15 | -16 tests |
| Factory Files | 12 | 11 | -1 deleted |
| API Routes | Versioned (/v1) | Simple (/api) | Simplified |
| Product Columns | 25 | 15 | -10 columns |
| Purchase Columns | 21 | 7 | -14 columns |
| Sale Columns | 20 | 8 | -12 columns |

---

**Status:** Ready for implementation  
**Estimated Time:** 2-3 hours for experienced developer  
**Risk:** Medium (database changes - recommend staging test first)

