# FRACA Inventory System - Refactoring Plan
## Simplification to Essential Features Only

**Date Created:** February 17, 2026  
**Current Phase:** Phase 3 (Core Business Logic)  
**Proposed Outcome:** Maintainable, focused system with 10 tables instead of 14

---

## Executive Summary

| Aspect | Before | After |
|--------|--------|-------|
| Database Tables | 14 | 10 |
| Model Files | 14 | 11 |
| Controllers | 8 | 5 |
| Unit Tests | 31 | 15 |
| API Routes | Versioned (/v1) | Simple (/api) |
| Permission System | Dual (JSON + pivot tables) | JSON only |
| Product Columns | 25 | 15 |
| Purchase Columns | 21 | 12 |
| Sale Columns | 20 | 11 |

---

## PART 1: DETAILED CHANGE ANALYSIS

### 1.1 Tables to Remove (4 tables)

#### permissions table
- **Reason:** Complexity not needed. Roles already have description. Use JSON for permissions.
- **Files Referencing:** Permission.php model, pivot relationships in Role
- **Data Loss:** None if unused

#### role_permission (pivot table)
- **Reason:** Consolidate into JSON column in roles table
- **Files Referencing:** Role.php relationships
- **Migration Action:** DROP TABLE role_permission

#### stock_adjustments table
- **Reason:** Merge into stock_history with type='adjustment'
- **Files Referencing:** StockAdjustment.php model, StockAdjustmentController
- **Migration Action:** 
  - Copy all stock_adjustments to stock_history with type='adjustment'
  - DROP TABLE stock_adjustments

### 1.2 Columns to Remove/Consolidate

#### Products Table (Remove 10 columns)
| Column | Reason | Workaround |
|--------|--------|-----------|
| unit_of_measurement | Nice-to-have, rarely used | Add later in Phase 5 |
| minimum_stock | Redundant with reorder_level | Use reorder_level only |
| initial_stock | Not tracked | Can calculate from purchase history |
| barcode | Add later with scanning feature | Phase 5 enhancement |
| image | UI feature, not core | Phase 5 feature |
| is_active | Useful but not critical | Add as migration Phase 4 |

#### Purchases Table (Remove 9 columns)
| Column | Reason | Workaround |
|--------|--------|-----------|
| reference_number | Generated extra ID | purchase_number is enough |
| invoice_number | Not needed without multi-invoice support | purchase_number serves purpose |
| delivery_date | Purchase is recorded when received | Only need purchase_date |
| shipping_cost | Adds complexity | Factor into unit_price if needed |
| tax_amount | Complex tax logic | Add later if needed |
| discount_amount | Complex pricing | Add later if needed |
| payment_method | Payment tracking not needed now | Add in Phase 5 |
| created_by | Redundant with user_id | user_id already tracks who recorded |

#### Purchase Items Table (Remove 2 columns)
| Column | Reason |
|--------|--------|
| tax_rate | Simplify costing |
| discount | Include in unit_price |

#### Sales Table (Remove 9 columns)
| Column | Reason | Workaround |
|--------|--------|-----------|
| reference_number | Generated extra ID | invoice_number is enough |
| due_date | Credit tracking, not needed | Add Phase 5 |
| shipping_cost | Factor into total if needed | Simple: no separate shipping |
| tax_amount | No tax calculation now | Add Phase 5 if needed |
| discount_amount | No discounting now | Add Phase 5 |
| payment_status | No payment tracking | Add Phase 5 |
| created_by | Redundant with user_id | user_id tracks |

#### Sale Items Table (Remove 2 columns)
| Column | Reason |
|--------|--------|
| tax_rate | Simplify costing |
| discount | Include in unit_price |

#### Stock History Table (Remove 2 columns, add 1)
| Removal | Reason |
|---------|--------|
| reference_type (morph column) | Replace with type='purchase'/'sale'/'adjustment' |
| reference_id_type (morph column) | Not needed |
| Add: transaction_type enum | Simple string: 'purchase', 'sale', 'adjustment' |

#### Alerts Table
- **Keep:** All columns (low_stock tracking is essential)

#### Users & Roles
- **Keep:** All columns
- **Modify Role:** Add JSON permissions column, remove belongsToMany(Permission)

### 1.3 Controllers to Modify/Remove

| Controller | Action | Changes |
|-----------|--------|---------|
| ProductController | Modify | Remove handling for: barcode, unit_of_measurement, image, minimum_stock |
| PurchaseController | Modify | Remove: tax/discount/shipping calculations, delivery_date, created_by |
| SaleController | Modify | Remove: payment_status, payment_method, due_date, tax/discount |
| StockAdjustmentController | DELETE | Merge functionality into other controllers or API endpoint |
| SupplierController | Keep | No changes needed |
| CustomerController | Keep | No changes needed |
| BaseController | Keep | Helper methods already generic |

### 1.4 Models to Update/Delete

| Model | Action | Changes |
|-------|--------|---------|
| Product.php | Update | Remove: unit_of_measurement, minimum_stock, initial_stock, barcode, image |
| Purchase.php | Update | Remove: reference_number, invoice_number, delivery_date, shipping_cost, tax_amount, discount_amount, payment_method, created_by |
| PurchaseItem.php | Update | Remove: tax_rate, discount |
| Sale.php | Update | Remove: reference_number, due_date, shipping_cost, tax_amount, discount_amount, payment_status, created_by |
| SaleItem.php | Update | Remove: tax_rate, discount |
| StockHistory.php | Update | Remove polymorphic, simplify to: product_id, quantity_change, transaction_type, reference_id, notes |
| Role.php | Update | Remove: belongsToMany(Permission), add: $casts = ['permissions' => 'array'] |
| Permission.php | DELETE | Remove file entirely |
| StockAdjustment.php | DELETE | Merge into StockHistory |
| Alert.php | Keep | No changes |
| Supplier.php | Keep | No changes |
| Customer.php | Keep | No changes |
| User.php | Keep | No changes |

### 1.5 Tests to Remove/Refactor

#### Tests to REMOVE (16 tests)
Remove all tests related to:
- Tax calculations
- Discount calculations
- Shipping cost handling
- Payment status tracking
- Polymorphic relationships
- Extra field validations

#### Tests to KEEP (15 tests)
**StockServiceTest (7 core tests)**
1. test_stock_incremented_after_purchase
2. test_stock_decremented_after_sale
3. test_overselling_prevented
4. test_low_stock_alert_generated
5. test_stock_history_recorded
6. test_manual_adjustment_logged
7. test_stock_never_negative

**PurchaseServiceTest (4 core tests)**
1. test_purchase_created_with_valid_data
2. test_stock_updated_on_purchase
3. test_purchase_order_number_generated
4. test_purchase_with_multiple_items

**SaleServiceTest (4 core tests)**
1. test_sale_created_with_valid_data
2. test_stock_decremented_on_sale
3. test_sale_prevented_insufficient_stock
4. test_sale_invoice_generated

---

## PART 2: STEP-BY-STEP EXECUTION PLAN

### Step 1: Backup Current Database
```bash
# Run at terminal
cd c:\xampp\htdocs\InventorySystem

# Windows PowerShell
$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
mysqldump -u root -p fraca_inventory > "backup_before_refactor_$timestamp.sql"

# Or manually through phpMyAdmin
# Export fraca_inventory to backup_before_refactor.sql
```

**Duration:** 2 minutes  
**Verification:** Confirm SQL file created in project root

---

### Step 2: Create Consolidation Migration
```bash
php artisan make:migration simplify_schema_consolidate_tables
```

**File Location:** database/migrations/2026_02_17_000001_simplify_schema_consolidate_tables.php

**Contents:** (See Part 3 below)

**Key Points:**
- Uses Schema::hasColumn() checks for safety
- Handles data migration: stock_adjustments → stock_history
- Drops unnecessary tables
- Removes extra columns
- Modifies stock_history structure

---

### Step 3: Update Models

#### 3.1 Update Product.php
- Remove: $fillable for deleted columns
- Remove: @property PHPDoc for deleted columns
- Keep: All relationships except nothing needs updating

#### 3.2 Update Purchase.php & PurchaseItem.php
- Remove: Columns from $fillable
- Remove: @property declarations
- Simplify: No tax/discount/shipping logic
- Keep: Basic validation, relationships

#### 3.3 Update Sale.php & SaleItem.php
- Remove: Columns from $fillable
- Remove: @property declarations
- Simplify: No payment tracking
- Keep: Stock validation logic

#### 3.4 Update StockHistory.php
- Remove: morphTo() relationship
- Add: Simple transaction_type field (string)
- Add: reference_id field (nullable)
- Simplify: No morph columns

#### 3.5 Update Role.php
- Remove: belongsToMany(Permission)
- Remove: hasPermission() method (replace with simple array check)
- Add: $casts = ['permissions' => 'array']
- Add: Simple permission checking via JSON

#### 3.6 DELETE Permission.php
- Remove file entirely
- Remove: Any references in ServiceProvider

---

### Step 4: Update Migrations

#### 4.1 Revert Recent 5 Migrations (or keep if data exists)
If you want to keep the extended schema:
- Leave `2026_02_16_000001` through `2026_02_16_000005` as-is
- New consolidation migration will remove extra columns

If you want to start fresh:
- `php artisan migrate:rollback` (if safe)
- Delete the 5 recent migration files
- Use consolidation migration as single source

**Recommended:** Keep the 5 migration files, add consolidation migration that fixes them.

---

### Step 5: Update Request Classes

#### 5.1 StoreProductRequest
Remove validation for:
- barcode, unit_of_measurement, minimum_stock, initial_stock, image

#### 5.2 StorePurchaseRequest
Remove validation for:
- reference_number, invoice_number, delivery_date
- shipping_cost, tax_amount, discount_amount
- payment_method, created_by

#### 5.3 StoreSaleRequest
Remove validation for:
- reference_number, due_date
- shipping_cost, tax_amount, discount_amount, payment_status
- created_by

---

### Step 6: Update Controllers

#### 6.1 ProductController
Replace:
```php
// REMOVE: Listing with extra columns
->where('unit_of_measurement', $request->unit)
->where('minimum_stock', $request->min_stock)

// KEEP: Core filters only
->where('category_id', $request->category_id)
->where('supplier_id', $request->supplier_id)
->where('is_active', $request->boolean('is_active'))

// REMOVE: Image upload handling
// KEEP: Basic CRUD

// REMOVE: Barcode generation
// KEEP: SKU validation
```

#### 6.2 PurchaseController
Simplify store() method:
```php
// REMOVE all this:
$totalTax = 0;
$totalDiscount = 0;
$shipping_cost = $request->shipping_cost ?? 0;

// KEEP only:
$totalAmount = 0;
foreach ($request->items as $item) {
    $itemTotal = $item['quantity'] * $item['unit_price'];
    $totalAmount += $itemTotal;
}

// REMOVE: delivery_date, reference_number, invoice_number, tax, discount handling
// KEEP: purchase_date, purchase_number, basic fields

// REMOVE: created_by field creation

// SIMPLIFY: Stock increment (no history morph relationships)
$product->increment('current_stock', $item['quantity']);
StockHistory::create([
    'product_id' => $product->id,
    'quantity_change' => $item['quantity'],
    'transaction_type' => 'purchase',
    'reference_id' => $purchase->id,
    'notes' => "Purchase: {$purchase->purchase_number}",
]);
```

#### 6.3 SaleController
Simplify store() method:
```php
// REMOVE: tax/discount/payment status calculations
// REMOVE: payment_method, payment_status, due_date

// KEEP: Stock validation (CRITICAL)
foreach ($request->items as $item) {
    $product = Product::find($item['product_id']);
    if ($product->current_stock < $item['quantity']) {
        // Return error
    }
}

// KEEP: Basic sale creation
// REMOVE: complex total calculations

// SIMPLIFY: Stock decrement and history
$product->decrement('current_stock', $item['quantity']);
StockHistory::create([
    'product_id' => $product->id,
    'quantity_change' => -$item['quantity'],
    'transaction_type' => 'sale',
    'reference_id' => $sale->id,
    'notes' => "Sale: {$sale->invoice_number}",
]);
```

#### 6.4 DELETE StockAdjustmentController
- Move any necessary logic to dedicated endpoint in ProductController
- Or create simple route that logs to StockHistory with type='adjustment'

---

### Step 7: Update Routes (routes/api.php)

Change from:
```php
Route::prefix('v1')->group(function () {
    // routes...
});
```

To:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
    Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
    // Remove: StockAdjustmentController route or replace with simple endpoint
});
```

---

### Step 8: Update Factories (database/factories/)

Update all factories to remove deleted fields:

**ProductFactory.php**
- Remove: barcode, unit_of_measurement, minimum_stock, initial_stock, image

**PurchaseFactory.php**
- Remove: reference_number, invoice_number, delivery_date, shipping_cost, tax_amount, discount_amount, created_by

**PurchaseItemFactory.php**
- Remove: tax_rate, discount

**SaleFactory.php**
- Remove: reference_number, due_date, shipping_cost, tax_amount, discount_amount, payment_status, created_by

**SaleItemFactory.php**
- Remove: tax_rate, discount

**StockHistoryFactory.php**
- Remove: reference_type (morph)
- Add: transaction_type (string)
- Add: reference_id (nullable)

**DELETE:** StockAdjustmentFactory.php

---

### Step 9: Refactor Tests

#### Delete Files:
- Remove test cases related to deleted features

#### Refactor StockServiceTest.php (11 tests → 7 tests)
**Keep:**
1. test_stock_incremented_after_purchase
2. test_stock_decremented_after_sale
3. test_overselling_prevented
4. test_low_stock_alert_generated
5. test_stock_history_recorded
6. test_manual_adjustment_logged
7. test_stock_never_negative

**Remove:**
- Tests for tax calculations
- Tests for polymorphic relationships
- Edge case tests for deleted features

#### Refactor PurchaseServiceTest.php (10 tests → 4 tests)
**Keep:**
1. test_purchase_created_with_valid_data (simplify assertions)
2. test_stock_updated_on_purchase
3. test_purchase_order_number_generated
4. test_purchase_with_multiple_items

**Remove:**
- Tests for tax/discount calculations
- Tests for delivery_date handling
- Tests for payment_method tracking

#### Refactor SaleServiceTest.php (10 tests → 4 tests)
**Keep:**
1. test_sale_created_with_valid_data (simplify)
2. test_stock_decremented_on_sale
3. test_sale_prevented_insufficient_stock
4. test_sale_invoice_generated

**Remove:**
- Tests for tax/discount
- Tests for payment tracking
- Tests for due_date

---

## PART 3: CONSOLIDATION MIGRATION

Create file: `database/migrations/2026_02_17_000001_simplify_schema_consolidate_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Migrate stock_adjustments data to stock_history
        if (Schema::hasTable('stock_adjustments')) {
            DB::statement("
                INSERT INTO stock_histories 
                (product_id, quantity_change, transaction_type, reference_id, notes, created_at, updated_at)
                SELECT 
                    product_id, 
                    (new_quantity - old_quantity) as quantity_change,
                    'adjustment' as transaction_type,
                    id as reference_id,
                    COALESCE(notes, adjustment_reason) as notes,
                    created_at,
                    updated_at
                FROM stock_adjustments
            ");
        }

        // Step 2: Drop permissions and pivot table
        if (Schema::hasTable('role_permission')) {
            Schema::dropIfExists('role_permission');
        }
        if (Schema::hasTable('permissions')) {
            Schema::dropIfExists('permissions');
        }

        // Step 3: Add permissions JSON column to roles if not exists
        if (Schema::hasTable('roles')) {
            if (!Schema::hasColumn('roles', 'permissions')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->json('permissions')->nullable()->after('description');
                });
            }
        }

        // Step 4: Simplify stock_histories table
        if (Schema::hasTable('stock_histories')) {
            // If polymorphic columns exist, drop them
            if (Schema::hasColumns('stock_histories', ['reference_type', 'reference_id_type'])) {
                Schema::table('stock_histories', function (Blueprint $table) {
                    $table->dropColumn(['reference_type', 'reference_id_type']);
                });
            }

            // Add transaction_type if not exists
            if (!Schema::hasColumn('stock_histories', 'transaction_type')) {
                Schema::table('stock_histories', function (Blueprint $table) {
                    $table->string('transaction_type')
                        ->nullable()
                        ->after('product_id')
                        ->comment('purchase, sale, adjustment');
                });
            }

            // Rename quantity to quantity_change if needed (existing data will work)
            // This is just for clarity in the codebase
        }

        // Step 5: Drop unnecessary columns from products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'unit_of_measurement')) {
                    $table->dropColumn('unit_of_measurement');
                }
                if (Schema::hasColumn('products', 'minimum_stock')) {
                    $table->dropColumn('minimum_stock');
                }
                if (Schema::hasColumn('products', 'initial_stock')) {
                    $table->dropColumn('initial_stock');
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $table->dropColumn('barcode');
                }
                if (Schema::hasColumn('products', 'image')) {
                    $table->dropColumn('image');
                }
            });
        }

        // Step 6: Simplify purchases table
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                $columnsToRemove = [
                    'reference_number',
                    'invoice_number',
                    'delivery_date',
                    'shipping_cost',
                    'tax_amount',
                    'discount_amount',
                    'payment_method',
                    'created_by',
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('purchases', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Step 7: Simplify purchase_items table
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_items', 'tax_rate')) {
                    $table->dropColumn('tax_rate');
                }
                if (Schema::hasColumn('purchase_items', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        }

        // Step 8: Simplify sales table
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                $columnsToRemove = [
                    'reference_number',
                    'due_date',
                    'shipping_cost',
                    'tax_amount',
                    'discount_amount',
                    'payment_status',
                    'created_by',
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('sales', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Step 9: Simplify sale_items table
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('sale_items', 'tax_rate')) {
                    $table->dropColumn('tax_rate');
                }
                if (Schema::hasColumn('sale_items', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        }

        // Step 10: Drop stock_adjustments table
        if (Schema::hasTable('stock_adjustments')) {
            Schema::dropIfExists('stock_adjustments');
        }
    }

    public function down(): void
    {
        // This migration is not reversible as it removes columns
        // Restore from backup if needed
        throw new \Exception('This migration cannot be reversed. Restore from backup if needed.');
    }
};
```

---

## PART 4: MODEL FILE CHANGES

### Product.php Changes

**Remove from $fillable:**
```php
'barcode', 'unit_of_measurement', 'minimum_stock', 'initial_stock', 'image'
```

**Remove from PHPDoc:**
```php
* @property string|null $barcode
* @property string|null $unit_of_measurement
* @property int|null $minimum_stock
* @property int|null $initial_stock
* @property string|null $image
```

### Purchase.php Changes

**Remove from $fillable:**
```php
'reference_number', 'invoice_number', 'delivery_date', 'shipping_cost', 
'tax_amount', 'discount_amount', 'payment_method', 'created_by'
```

**Simplify store logic:**
- Remove all tax/discount/shipping calculations
- Keep only: supplier_id, user_id, purchase_number, purchase_date, total_amount, notes, status

### Sale.php Changes

**Remove from $fillable:**
```php
'reference_number', 'due_date', 'shipping_cost', 'tax_amount', 
'discount_amount', 'payment_status', 'created_by'
```

**Simplify:** Keep only essential payment fields, remove complex tracking

### StockHistory.php Simplification

**Before:**
```php
public function historyable()
{
    return $this->morphTo();
}
```

**After:**
```php
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
```

### Role.php Changes

**Remove:**
```php
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'role_permission');
}
```

**Add:**
```php
protected $casts = [
    'permissions' => 'array',
];

public function hasPermission($permission)
{
    return in_array($permission, $this->permissions ?? []);
}
```

### Delete Permission.php
Remove file entirely.

---

## PART 5: EXECUTION CHECKLIST

### Pre-Execution
- [ ] Backup database
- [ ] Create branch: `git checkout -b refactor/simplify-schema`
- [ ] Verify no uncommitted changes: `git status`

### Phase 1: Migrations (15 min)
- [ ] Run: `php artisan make:migration simplify_schema_consolidate_tables`
- [ ] Copy migration code (Part 3 above)
- [ ] Test: `php artisan migrate --dry-run` (verify changes)
- [ ] Run: `php artisan migrate`
- [ ] Verify: Check database schema in MySQL

### Phase 2: Model Updates (20 min)
- [ ] Update: Product.php
- [ ] Update: Purchase.php, PurchaseItem.php
- [ ] Update: Sale.php, SaleItem.php
- [ ] Update: StockHistory.php
- [ ] Update: Role.php
- [ ] Delete: Permission.php
- [ ] Run: `php -l app/Models/*.php` (syntax check)

### Phase 3: Controller Updates (25 min)
- [ ] Update: ProductController.php
- [ ] Update: PurchaseController.php
- [ ] Update: SaleController.php
- [ ] Update: routes/api.php (remove /v1)
- [ ] Delete/simplify: StockAdjustmentController.php
- [ ] Run: `php -l app/Http/Controllers/Api/*.php`

### Phase 4: Request Classes (10 min)
- [ ] Update: StoreProductRequest.php
- [ ] Update: StorePurchaseRequest.php
- [ ] Update: StoreSaleRequest.php

### Phase 5: Factory & Test Updates (30 min)
- [ ] Update: All factories
- [ ] Delete: StockAdjustmentFactory.php
- [ ] Refactor: StockServiceTest.php (11 → 7 tests)
- [ ] Refactor: PurchaseServiceTest.php (10 → 4 tests)
- [ ] Refactor: SaleServiceTest.php (10 → 4 tests)
- [ ] Run: `php artisan test` (verify all tests pass)

### Phase 6: Verification & Testing (20 min)
- [ ] Run: `composer dump-autoload`
- [ ] Run: `php artisan optimize:clear`
- [ ] Run: `php artisan test --coverage`
- [ ] Manual test: API endpoints via Postman/Insomnia
- [ ] Verify: Core workflows (purchase → stock increase, sale → stock decrease)

### Phase 7: Commit & Cleanup (10 min)
- [ ] Commit: `git add . && git commit -m "refactor: simplify schema and remove unnecessary features"`
- [ ] Push: `git push origin refactor/simplify-schema`
- [ ] Create: Pull request
- [ ] Delete: Old backup (optional)

**Total Time: ~2 hours**

---

## PART 6: ROLLBACK PLAN

If anything goes wrong:

### Option 1: Database Rollback
```bash
# Stop the application
# Restore from backup
mysql -u root fraca_inventory < backup_before_refactor_2026-02-17_HHMMSS.sql

# Revert git changes
git checkout develop  # or main if you didn't create branch
$env:COMPOSE_COMMAND = "docker-compose"  # or skip if not using Docker
```

### Option 2: Partial Rollback (if you tested migrations)
```bash
# Only rollback migrations, keep code changes
php artisan migrate:rollback

# Then revert git
git reset --hard HEAD~1
```

### Option 3: Keep Both Versions
- Maintain current `main` branch
- Keep refactored code in `refactor/simplify-schema` branch
- Can switch between them if needed

---

## PART 7: POST-REFACTORING CLEANUP

### Update Documentation
- [ ] Update DEVELOPMENT_PROGRESS.md with refactoring note
- [ ] Update SYSTEM_OVERVIEW_FOR_DEEPSEEK.md with new schema
- [ ] Update README.md with simplified feature list
- [ ] Update development_reference.md

### Update Seeders
- [ ] RoleSeeder.php - add sample permissions JSON
- [ ] Any other seeders using deleted fields

### Update Comments/TODOs
- [ ] Search for references to deleted features
- [ ] Remove or update any TODO comments

### Performance Check
- [ ] Verify indexes are still optimal
- [ ] Check query performance on report endpoints
- [ ] Monitor application response times

---

## PART 8: SCHEMA COMPARISON

### Tables: Before vs After

```
BEFORE (14 tables):
├── users
├── roles
├── permissions
├── role_permission
├── categories
├── suppliers
├── customers
├── products
├── purchases
├── purchase_items
├── sales
├── sale_items
├── stock_histories
├── stock_adjustments
└── alerts

AFTER (10 tables):
├── users
├── roles (+ permissions JSON column)
├── categories
├── suppliers
├── customers
├── products (simplified)
├── purchases (simplified)
├── purchase_items (simplified)
├── sales (simplified)
├── sale_items (simplified)
├── stock_histories (simplified)
└── alerts
```

### Column Count Summary

```
Product: 25 → 15 columns (-10)
Purchase: 21 → 12 columns (-9)
PurchaseItem: 5 → 3 columns (-2)
Sale: 20 → 11 columns (-9)
SaleItem: 5 → 3 columns (-2)
StockHistory: 9 → 5 columns (-4) + simpler structure
Total: Reduction of ~36 redundant columns
```

---

## PART 9: QUICK REFERENCE COMMANDS

```bash
# Prepare
cd c:\xampp\htdocs\InventorySystem
git status
git checkout -b refactor/simplify-schema

# Backup
mysqldump -u root fraca_inventory > backup_$(date +%F_%T).sql

# Execute
php artisan make:migration simplify_schema_consolidate_tables
# (insert migration code from Part 3)

php artisan migrate --dry-run  # Test first
php artisan migrate             # Execute

# Update code
# (Follow Part 4, 5, 6)

# Test
composer dump-autoload
php artisan optimize:clear
php artisan test

# Commit
git add .
git commit -m "refactor: simplify schema and remove unnecessary complexity"
git push origin refactor/simplify-schema
```

---

## DELIVERABLES CHECKLIST

- [ ] Migration file with consolidation logic
- [ ] Updated model files (11 models)
- [ ] Updated controller files (5 controllers)
- [ ] Updated request validation classes (3 classes)
- [ ] Updated test suite (15 tests)
- [ ] Updated factory files (12 factories)
- [ ] Updated routes file (simple /api routes)
- [ ] This comprehensive refactoring plan document
- [ ] Execution checklist
- [ ] Rollback procedures
- [ ] Updated documentation

---

**Status:** Ready for Execution  
**Estimated Duration:** 2-3 hours  
**Risk Level:** Medium (database changes, recommend testing in staging first)  
**Benefits:** Simpler codebase, faster development, reduced maintenance burden

