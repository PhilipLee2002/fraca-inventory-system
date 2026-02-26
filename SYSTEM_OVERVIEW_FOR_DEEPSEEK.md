# FRACA SERVCOM Inventory Management System - Comprehensive System Overview

**Project Status:** Phase 3 (Core Business Logic) - 80% Complete  
**Current Date:** February 17, 2026  
**Technology Stack:** PHP Laravel 12, MySQL, JavaScript, Blade Templates  
**Repository:** https://github.com/PhilipLee2002/fraca-inventory-system (Branch: main)

---

## 1. PROJECT OVERVIEW

### Purpose & Goals
- **Client:** FRACA SERVCOM (furniture & hardware supplies)
- **Goal:** Web-based inventory management system for medium-sized SME
- **Primary Objectives:**
  - Automate stock tracking and control
  - Low-stock alerts and notifications
  - Sales & Purchase order management
  - Basic reporting and CSV/PDF exports
  - Role-based user access control
  - Audit trails for all transactions

### Key Features
- ✅ Product CRUD with barcode/SKU support
- ✅ Stock tracking with real-time updates
- ✅ Purchase orders with line items
- ✅ Sales invoices with line items
- ✅ User authentication and role-based access (Admin/Staff)
- ✅ Stock history audit trail (polymorphic relationships)
- ✅ Low-stock alert management
- ✅ API-first design with JSON responses
- ✅ Pagination, filtering, searching

---

## 2. TECHNOLOGY STACK

| Component | Technology | Version |
|-----------|-----------|---------|
| Backend Framework | Laravel | 12.x |
| PHP Version | PHP | 8.1+ |
| Database | MySQL | 8.0+ |
| Authentication | Laravel Breeze + Sanctum | Latest |
| ORM | Eloquent | Built-in |
| Frontend | HTML5, CSS3, JavaScript | Vanilla + Vue (optional) |
| Templates | Blade | Built-in |
| CSS Framework | Tailwind CSS | 3.x |
| Testing | PHPUnit | 11.5.46 |
| PDF Generation | Laravel DOMPDF | 3.x |
| CSV Handling | League CSV | 9.x |

### Development Environment
- **Host:** XAMPP / Local Laravel installation
- **Server:** Apache (XAMPP) / Laravel Artisan
- **Database:** MySQL 8.0+ (localhost:3306)
- **Port:** 8000 (default Laravel development server)
- **Location:** C:\xampp\htdocs\InventorySystem

---

## 3. DATABASE SCHEMA & MODELS

### 13 Core Tables & Eloquent Models

#### 1. **Products** Table
```php
// app/Models/Product.php
Fields:
- id (Primary Key)
- name (string)
- sku (string, unique)
- barcode (string, unique)
- description (text, nullable)
- cost_price (decimal)
- selling_price (decimal)
- current_stock (integer)
- initial_stock (integer, nullable)
- reorder_level (integer, nullable)
- minimum_stock (integer, nullable)
- unit_of_measurement (string, nullable)
- category_id (foreign key → categories)
- supplier_id (foreign key → suppliers)
- image (string, nullable) - image path
- is_active (boolean)
- timestamps (created_at, updated_at)

Relationships:
- belongsTo(Category)
- belongsTo(Supplier)
- hasMany(PurchaseItem)
- hasMany(SaleItem)
- hasMany(StockHistory)
- hasMany(Alert)
- hasMany(StockAdjustment)

Helper Methods:
- logStockMovement($type, $quantity_change, $notes)
```

#### 2. **Users** Table
```php
// app/Models/User.php
Fields:
- id
- name
- email (unique)
- email_verified_at (nullable)
- password (hashed)
- remember_token
- role_id (foreign key → roles)
- timestamps

Relationships:
- belongsTo(Role)
- hasMany(Purchase)
- hasMany(Sale)
- hasMany(StockAdjustment) - as 'adjusted_by'
```

#### 3. **Roles** Table
```php
Fields:
- id
- name (unique)
- description (nullable)
- timestamps

Relationships:
- hasMany(User)
- belongsToMany(Permission) - pivot: role_permission
- hasPermission($permission_name) - helper method
```

#### 4. **Permissions** Table
```php
Fields:
- id
- name (unique)
- description (nullable)
- timestamps

Relationships:
- belongsToMany(Role)
```

#### 5. **Categories** Table
```php
Fields:
- id
- name
- description (nullable)
- timestamps

Relationships:
- hasMany(Product)
```

#### 6. **Suppliers** Table
```php
Fields:
- id
- name
- contact_person (nullable)
- email
- phone
- address
- city
- state
- postal_code
- country
- payment_terms (nullable)
- is_active (boolean)
- timestamps

Relationships:
- hasMany(Product)
- hasMany(Purchase)
```

#### 7. **Customers** Table
```php
Fields:
- id
- first_name
- last_name
- email
- phone
- address
- city
- state
- postal_code
- country
- is_active (boolean)
- timestamps

Relationships:
- hasMany(Sale)
```

#### 8. **Purchases** Table
```php
// app/Models/Purchase.php
Fields:
- id
- supplier_id (foreign key)
- user_id (foreign key → User who recorded)
- purchase_number (auto-generated)
- reference_number (nullable)
- invoice_number (nullable)
- purchase_date (date, nullable)
- delivery_date (date, nullable)
- total_amount (decimal)
- shipping_cost (decimal)
- tax_amount (decimal)
- discount_amount (decimal)
- payment_method (string, nullable: 'cash', 'check', 'transfer')
- status (string: 'pending', 'received', 'cancelled')
- notes (text, nullable)
- created_by (foreign key → User, nullable)
- timestamps

Relationships:
- belongsTo(Supplier)
- belongsTo(User)
- hasMany(PurchaseItem)
```

#### 9. **Purchase Items** Table
```php
// app/Models/PurchaseItem.php
Fields:
- id
- purchase_id (foreign key)
- product_id (foreign key)
- quantity (integer)
- unit_price (decimal)
- tax_rate (decimal, default 0)
- discount (decimal, default 0)
- total (decimal - calculated)
- timestamps

Relationships:
- belongsTo(Purchase)
- belongsTo(Product)
```

#### 10. **Sales** Table
```php
// app/Models/Sale.php
Fields:
- id
- customer_id (foreign key)
- user_id (foreign key → User who recorded)
- invoice_number (generated)
- sale_date (date, nullable)
- due_date (date, nullable)
- reference_number (generated)
- total_amount (decimal)
- shipping_cost (decimal)
- tax_amount (decimal)
- discount_amount (decimal)
- payment_method (string)
- payment_status (string: 'paid', 'pending', 'overdue')
- status (string: 'pending', 'completed', 'cancelled')
- notes (text, nullable)
- created_by (foreign key → User, nullable)
- timestamps

Relationships:
- belongsTo(Customer)
- belongsTo(User)
- hasMany(SaleItem)
```

#### 11. **Sale Items** Table
```php
// app/Models/SaleItem.php
Fields:
- id
- sale_id (foreign key)
- product_id (foreign key)
- quantity (integer)
- unit_price (decimal)
- tax_rate (decimal, default 0)
- discount (decimal, default 0)
- total (decimal - calculated)
- timestamps

Relationships:
- belongsTo(Sale)
- belongsTo(Product)
```

#### 12. **Stock Histories** Table
```php
// app/Models/StockHistory.php
Fields:
- id
- product_id (foreign key)
- quantity (integer - can be positive/negative)
- transaction_type (string: 'purchase', 'sale', 'adjustment')
- reference_id (nullable - ID of Purchase/Sale/Adjustment)
- reference_type (nullable - model class name)
- notes (text, nullable)
- created_at, updated_at

Relationships:
- belongsTo(Product)
- morphTo() - polymorphic to Purchase, Sale, or StockAdjustment
```

#### 13. **Alerts** Table
```php
// app/Models/Alert.php
Fields:
- id
- product_id (foreign key)
- alert_type (string: 'low_stock', 'expiry_warning', 'reorder_point')
- severity (string: 'low', 'medium', 'high', 'critical')
- message (text)
- is_resolved (boolean)
- resolved_at (timestamp, nullable)
- timestamps

Relationships:
- belongsTo(Product)
```

#### 14. **Stock Adjustments** Table
```php
// app/Models/StockAdjustment.php
Fields:
- id
- product_id (foreign key)
- old_quantity (integer)
- new_quantity (integer)
- adjustment_reason (string: 'physical_count', 'damage', 'loss', 'correction', 'return')
- notes (text, nullable)
- adjusted_by (foreign key → User)
- timestamps

Relationships:
- belongsTo(Product)
- belongsTo(User, 'adjusted_by')
```

### Database Constraints
- Foreign key constraints on all relationships
- Unique constraints on: email (Users), sku (Products), barcode (Products), name (Roles)
- Cascading deletes for data integrity
- Indexes on frequently queried fields: sku, barcode, category_id, created_at

---

## 4. FOLDER STRUCTURE

```
InventorySystem/
├── app/
│   ├── Console/
│   │   └── Kernel.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Authentication controllers
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   └── ... (email verification, password confirm)
│   │   │   ├── Api/
│   │   │   │   ├── BaseController.php   # Base class with JSON helpers
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── PurchaseController.php
│   │   │   │   ├── SaleController.php
│   │   │   │   ├── StockAdjustmentController.php
│   │   │   │   └── SupplierController.php
│   │   │   ├── UserController.php       # User management
│   │   │   ├── ProfileController.php    # Self-service profile
│   │   │   └── DashboardController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php            # Role validation
│   │   │   ├── CheckPermission.php      # Permission validation
│   │   │   └── ... (auth, verify, etc.)
│   │   └── Requests/
│   │       ├── StoreProductRequest.php
│   │       ├── StorePurchaseRequest.php
│   │       ├── StoreSaleRequest.php
│   │       └── ... (update request classes)
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Purchase.php
│   │   ├── PurchaseItem.php
│   │   ├── Sale.php
│   │   ├── SaleItem.php
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Category.php
│   │   ├── Supplier.php
│   │   ├── Customer.php
│   │   ├── StockHistory.php
│   │   ├── Alert.php
│   │   └── StockAdjustment.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   └── ... (other config files)
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 2026_01_21_225159_create_roles_table.php
│   │   ├── 2026_01_21_225203_add_role_id_to_users_table.php
│   │   ├── 2026_01_21_225204_create_categories_table.php
│   │   ├── 2026_01_21_225205_create_suppliers_table.php
│   │   ├── 2026_01_21_225206_create_customers_table.php
│   │   ├── 2026_01_21_225207_create_products_table.php
│   │   ├── 2026_01_21_225208_create_purchases_table.php
│   │   ├── 2026_01_21_225209_create_purchase_items_table.php
│   │   ├── 2026_01_21_225210_create_sales_table.php
│   │   ├── 2026_01_21_225211_create_sale_items_table.php
│   │   ├── 2026_01_21_225212_create_stock_histories_table.php
│   │   ├── 2026_01_21_225213_create_alerts_table.php
│   │   ├── 2026_01_25_234602_create_permission_tables.php
│   │   ├── 2026_01_26_000026_rename_permissions_to_description_in_roles_table.php
│   │   ├── 2026_02_01_000000_create_stock_adjustments_table.php
│   │   ├── 2026_02_16_000001_alter_products_add_stock_columns.php
│   │   ├── 2026_02_16_000002_alter_purchases_add_detail_columns.php
│   │   ├── 2026_02_16_000003_alter_sales_add_detail_columns.php
│   │   ├── 2026_02_16_000004_alter_purchase_items_add_columns.php
│   │   └── 2026_02_16_000005_alter_sale_items_add_columns.php
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── ProductFactory.php
│   │   ├── SupplierFactory.php
│   │   ├── CustomerFactory.php
│   │   ├── PurchaseFactory.php
│   │   ├── SaleFactory.php
│   │   └── ... (other factories)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       └── PermissionSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php            # Main layout
│   │   │   ├── navigation.blade.php
│   │   │   ├── guest.blade.php
│   │   │   └── footer.blade.php
│   │   ├── auth/                        # Auth views (login, register, etc.)
│   │   ├── dashboard.blade.php
│   │   ├── products/                    # Product views (CRUD)
│   │   ├── sales/                       # Sales views
│   │   ├── purchases/                   # Purchase views
│   │   └── users/                       # User management views
│   ├── css/
│   │   ├── app.css
│   │   └── (tailwind config)
│   └── js/
│       ├── app.js
│       └── (bootstrap, alpine, etc.)
├── routes/
│   ├── api.php                          # API routes (Sanctum protected)
│   ├── web.php                          # Web routes (session auth)
│   ├── auth.php                         # Auth routes (login, register, etc.)
│   └── console.php
├── storage/
│   ├── app/                             # File uploads
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Unit/
│   │   ├── StockServiceTest.php         # Stock service tests (11 tests)
│   │   ├── PurchaseServiceTest.php      # Purchase service tests (10 tests)
│   │   └── SaleServiceTest.php          # Sales service tests (10 tests)
│   ├── Feature/
│   │   └── Auth/                        # Auth feature tests
│   └── TestCase.php
├── vendor/                              # Dependencies (Composer)
├── .env                                 # Environment configuration
├── .env.example                         # Example .env
├── artisan                              # Artisan CLI
├── composer.json                        # PHP dependencies
├── composer.lock
├── package.json                         # JavaScript dependencies
├── phpunit.xml                          # PHPUnit configuration
├── vite.config.js                       # Vite bundler config
├── tailwind.config.js                   # Tailwind CSS config
├── postcss.config.js
├── README.md
├── DEVELOPMENT_PROGRESS.md              # Progress tracking document
├── development_reference.md             # Complete feature specification
└── SYSTEM_OVERVIEW_FOR_DEEPSEEK.md     # This file
```

---

## 5. API ROUTES & ENDPOINTS

### Base URL
```
http://localhost:8000/api/v1
```

### Route Structure (routes/api.php)
```php
Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Protected endpoints (require auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Resource routes (CRUD)
        Route::apiResource('products', ProductController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
        Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
        
        // Special endpoints
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store']);
        Route::get('/products/{product}/low-stock', [ProductController::class, 'lowStock']);
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales']);
            Route::get('/purchases', [ReportController::class, 'purchases']);
            Route::get('/stock-levels', [ReportController::class, 'stockLevels']);
            Route::get('/inventory-valuation', [ReportController::class, 'inventoryValuation']);
        });
    });
});
```

### Key Endpoints

#### Products API
- `GET /products` - List products (with pagination, filters, search)
- `POST /products` - Create product
- `GET /products/{id}` - Get product details
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product
- `GET /products?category_id=1` - Filter by category
- `GET /products?search=name` - Search by name/SKU/description

#### Purchases API
- `GET /purchases` - List purchases
- `POST /purchases` - Create purchase with line items
- `GET /purchases/{id}` - Get purchase details
- `POST /purchases/{id}/status` - Update purchase status

#### Sales API
- `GET /sales` - List sales
- `POST /sales` - Create sale with stock validation
- `GET /sales/{id}` - Get sale details
- `POST /sales/{id}/payment` - Record payment

#### Stock Management
- `POST /stock-adjustments` - Manual stock correction
- `GET /products/low-stock` - Get low-stock products

#### Reports
- `GET /reports/sales` - Sales report
- `GET /reports/purchases` - Purchase report
- `GET /reports/stock-levels` - Stock levels report
- `GET /reports/inventory-valuation` - Inventory value report

---

## 6. KEY CONTROLLERS

### BaseController (app/Http/Controllers/Api/BaseController.php)
```php
// Base class for all API controllers with JSON response helpers

public function sendSuccess($data, $message, $code = 200)       // Standard success
public function sendError($message, $data = null, $code = 400)  // Error response
public function sendCreated($data, $message)                    // 201 Created
public function sendUpdated($data, $message)                    // Update success
public function sendDeleted($message)                           // Delete success
public function sendPaginated($data, $message)                  // Paginated response
```

### ProductController (200+ lines)
**Key Methods:**
- `index()` - List with filters (category, supplier, active status, search, pagination)
- `store()` - Create product with image upload
- `show()` - Get product with stock history
- `update()` - Edit product with image handling
- `destroy()` - Delete product (checks for transactions first)
- `lowStock()` - Get low-stock products

**Special Features:**
- Image upload handling with storage
- withSum() for calculating stock totals
- Multiple filter options
- Stock statistics calculation

### PurchaseController (200+ lines)
**Key Methods:**
- `index()` - List purchases with filters and pagination
- `store()` - **CRITICAL BUSINESS LOGIC**
  - Creates purchase record
  - Creates purchase items
  - **Increments product stock** for each item
  - Creates StockHistory records
  - Uses database transactions for atomicity
- `show()` - Get purchase with items and relationships
- `updateStatus()` - Change purchase status with stock reversal logic

**Transaction Safety:**
```php
DB::beginTransaction();
try {
    // Create purchase
    // Create items
    // Update stock
    // Log stock history
    DB::commit();
} catch {
    DB::rollBack();
}
```

### SaleController (200+ lines)
**Key Methods:**
- `index()` - List sales with filters
- `store()` - **CRITICAL BUSINESS LOGIC**
  - Validates stock availability
  - Creates sale record
  - Creates sale items
  - **Decrements product stock**
  - Creates StockHistory records
  - Triggers low-stock alerts
  - Uses transactions for safety
- `show()` - Get sale with items
- Update status/payment - Payment tracking

**Key Difference from Purchases:**
- Stock decreases (negative quantity in history)
- Validates sufficient stock before creating
- Checks if stock falls below minimum

### StockAdjustmentController
**Methods:**
- `store()` - Create manual adjustment with reason
- Logs adjustment to StockHistory
- Updates product stock

---

## 7. REQUEST VALIDATION CLASSES

### StorePurchaseRequest
Validates:
- supplier_id (required, exists in suppliers table)
- items[] array with product_id, quantity, unit_price, tax_rate, discount
- purchase_date, delivery_date (dates)
- reference_number, invoice_number (strings)
- shipping_cost, tax_amount, discount_amount (decimals)
- status (pending, received, cancelled)
- notes (optional)

### StoreSaleRequest
Validates:
- customer_id (required, exists)
- items[] array with product_id, quantity, unit_price
- sale_date, due_date (dates)
- payment_method, payment_status
- Ensures product exists and has sufficient stock
- Calculates totals correctly

### StoreProductRequest
Validates:
- name (required)
- sku, barcode (required, unique)
- cost_price, selling_price (required, numeric)
- category_id, supplier_id (exist in DB)
- image (optional, image file)
- description, unit_of_measurement

---

## 8. TEST SUITE

### Unit Tests (31 total test methods)

#### StockServiceTest (11 tests - 223 lines)
1. test_stock_incremented_after_purchase
2. test_stock_decremented_after_sale
3. test_overselling_prevented
4. test_manual_stock_adjustment_logged
5. test_low_stock_alert_generated
6. test_no_low_stock_alert_when_stock_sufficient
7. test_stock_history_polymorphic_tracking
8. test_purchase_transaction_atomicity
9. test_stock_never_goes_negative
10-11. Additional coverage tests

#### PurchaseServiceTest (10 tests - 333 lines)
1. test_stock_incremented_after_purchase
2. test_purchase_total_calculated_correctly
3. test_purchase_created_with_valid_data
4. test_purchase_order_number_auto_generated
5. test_purchase_relationships
6. test_purchase_with_multiple_products
7. test_purchase_status_transitions
8. test_purchase_cancellation_transaction_safety
9. test_purchase_transaction_creates_all_records
10. Additional workflow test

#### SaleServiceTest (10 tests - 307 lines)
1. test_sale_created_with_valid_data
2. test_stock_decremented_on_valid_sale
3. test_sale_prevented_when_insufficient_stock
4. test_invoice_number_auto_generated
5. test_sale_total_calculated_correctly
6. test_sale_relationships
7. test_complete_sales_workflow
8. test_sale_with_multiple_products
9-10. Additional edge case tests

### Test Framework
- **Framework:** PHPUnit 11.5.46
- **Database:** RefreshDatabase trait (in-memory/test DB)
- **Factories:** Model factories for creating test data
- **Assertions:** Standard PHPUnit assertions + custom helpers

### Running Tests
```bash
php artisan test                                    # Run all tests
php artisan test tests/Unit/StockServiceTest.php   # Run specific test
php artisan test --coverage                        # With code coverage
```

---

## 9. CURRENT DEVELOPMENT STATUS

### Phase Completion Summary

| Phase | Status | Completion | Key Deliverables |
|-------|--------|-----------|------------------|
| Phase 0: Setup | ✅ Complete | Jan 21, 2026 | Repo, docs, packages |
| Phase 1: Database & Models | ✅ Complete | Jan 21, 2026 | 14 migrations, 14 models |
| Phase 2: Auth & Authorization | ✅ Complete | Jan 27, 2026 | User/Role/Permission system |
| Phase 3: Core Business Logic | 🟡 80% Complete | In Progress | Controllers, tests, API |
| Phase 4: API & Testing | ⏳ Ready | Pending | Full test execution |
| Phase 5: Frontend | ⏳ Planned | After Phase 4 | All UI views |

### Phase 3 Completion Status (Current)

**✅ Completed:**
- Database schema with 5 new migration files (executed successfully)
- All 14 Eloquent models with relationships
- 5 API controllers (Product, Purchase, Sale, StockAdjustment, Supplier)
- Request validation classes
- 31 comprehensive unit tests (uncommented and ready)
- Model factories for all entities (12 factories)
- API routes configured with Sanctum authentication
- Database transaction safety for critical operations
- Stock history polymorphic relationships

**🟡 In Progress:**
- Running complete test suite
- Model HasFactory traits (partially added)
- Final factory configurations

**⏳ Still Needed:**
- Frontend Blade templates for all CRUD operations
- Dashboard with widgets and statistics
- Role-based view restrictions
- PDF/CSV report generation
- Email notifications
- Barcode generation/scanning UI
- Stock reconciliation tools

---

## 10. KEY DESIGN DECISIONS

### 1. API-First Architecture
- JSON responses for all endpoints
- Database transaction safety (DB::beginTransaction/commit/rollback)
- Proper HTTP status codes (200, 201, 400, 422, 500)
- Sanctum token-based authentication for APIs

### 2. Stock Management Strategy
- **Stock Increment:** On purchase creation, product stock increases
- **Stock Decrement:** On sale creation, product stock decreases
- **Validation:** Sale cannot be created if stock insufficient
- **Audit Trail:** Every change logged to StockHistory with polymorphic relationships
- **History:** Can track back to originating Purchase/Sale/Adjustment

### 3. Database Relationships
- **Many-to-Many:** Role ↔ Permission (via role_permission pivot)
- **Polymorphic:** StockHistory can reference Purchase, Sale, or StockAdjustment
- **Foreign Keys:** All transactions linked to User who created them
- **Cascading:** Deletes only on leaf nodes (items, not parent transactions)

### 4. Transaction Safety
- Every purchase/sale creation wrapped in transaction
- All-or-nothing atomicity: if any item fails, entire transaction rolls back
- Stock reversal if purchase is cancelled after being received
- Error logging and rollback on exceptions

### 5. Validation Strategy
- **Request-level:** Form validation in dedicated Request classes
- **Model-level:** Business logic in controllers with guard clauses
- **Database-level:** Foreign keys and unique constraints
- **Stock-level:** Check before decrement, prevent negative stock

---

## 11. COMMON CODE PATTERNS

### Creating a Resource with Transaction
```php
DB::beginTransaction();
try {
    $record = Model::create($validated_data);
    
    foreach ($items as $item) {
        $record->items()->create($item);
        // Update related models
    }
    
    DB::commit();
    return $this->sendCreated($record, 'Success message');
} catch (\Exception $e) {
    DB::rollBack();
    return $this->sendError('Error: ' . $e->getMessage());
}
```

### Filtering & Pagination
```php
$query = Model::with(['relationships'])
    ->when($request->has('filter'), function($q) use ($request) {
        $q->where('field', $request->filter);
    })
    ->orderBy('created_at', 'desc')
    ->paginate($request->get('per_page', 20));

return $this->sendPaginated($query, 'Message');
```

### Stock Update with History
```php
$product->increment('current_stock', $quantity);
StockHistory::create([
    'product_id' => $product->id,
    'quantity' => $quantity,
    'transaction_type' => 'purchase',
    'reference_type' => Purchase::class,
    'reference_id' => $purchase->id,
]);
```

---

## 12. ENVIRONMENT SETUP

### .env Configuration
```
APP_NAME="FRACA Inventory System"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fraca_inventory
DB_USERNAME=root
DB_PASSWORD=

MAIL_FROM_ADDRESS=no-reply@fraca.local

APP_URL=http://localhost:8000
```

### Database Setup
```bash
# Create database
mysql -u root -e "CREATE DATABASE fraca_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

# Or refresh (development only, DELETES all data)
php artisan migrate:refresh --seed
```

### Running the System
```bash
# Start development server
php artisan serve

# In another terminal, watch for changes (optional)
npm run dev

# Access at: http://localhost:8000
```

---

## 13. RECENT CHANGES (Feb 16-17, 2026)

### Schema Extensions (5 new migrations executed)
- Added missing columns to `products` table: current_stock, initial_stock, minimum_stock, unit_of_measurement, is_active
- Added missing columns to `purchases` table: purchase_date, delivery_date, reference_number, invoice_number, shipping_cost, tax_amount, discount_amount, created_by, payment_method
- Added columns to `sales` table: sale_date, due_date, reference_number, shipping_cost, tax_amount, discount_amount, payment_status, created_by
- Added columns to `purchase_items` and `sale_items`: unit_price, tax_rate, discount
- All migrations use conditional checks (Schema::hasColumn) to prevent re-run errors

### Model Updates
- Added `@property` PHPDoc declarations to all models for IDE type-hinting
- Added `HasFactory` trait to all models for test factories
- Created 12 model factories for testing

### Test Preparations
- All 31 test methods uncommented and have active assertions
- Tests use model factories for test data generation

---

## 14. KNOWN ISSUES & BLOCKERS

### Current Issues
1. **Test Execution:** Models need HasFactory trait fully configured
2. **Schema Alignment:** 28 new columns added to support controller logic
3. **Missing Factories:** Created but need verification

### Fixed Issues
- ✅ Schema-code mismatch (19 identified and fixed)
- ✅ Model PHPDoc declarations (all added)
- ✅ Transaction safety (all critical paths wrapped)
- ✅ Stock validation (prevents overselling)

---

## 15. HELPFUL COMMANDS

```bash
# Artisan commands
php artisan migrate                    # Run migrations
php artisan migrate:refresh --seed     # Reset database (dev only!)
php artisan db:seed                    # Run seeders
php artisan tinker                     # Interactive PHP shell
php artisan route:list                 # Show all routes
php artisan make:model ModelName -m    # Create model + migration

# Testing
php artisan test                       # Run all tests
php artisan test tests/Unit/File.php   # Run specific file
php artisan test --coverage            # With code coverage

# Development
php artisan serve                      # Start dev server
php artisan optimize:clear             # Clear caches
composer dump-autoload                 # Regenerate autoload

# Database
mysql -u root -e "SHOW DATABASES;"     # List databases
mysql -u root fraca_inventory ...      # Direct SQL execution
php artisan tinker                     # Query builder in PHP

# Code Quality
vendor/bin/php-cs-fixer fix            # Format PHP code
php -l app/Models/Product.php          # Check syntax
```

---

## 16. QUICK REFERENCE: KEY FILES

| Purpose | File Path |
|---------|-----------|
| Database Schema | database/migrations/* |
| Core Models | app/Models/*.php |
| API Controllers | app/Http/Controllers/Api/* |
| Request Validation | app/Http/Requests/* |
| API Routes | routes/api.php |
| Web Routes | routes/web.php |
| Tests | tests/Unit/*.php |
| Factories | database/factories/* |
| Configuration | config/* |

---

## 17. NEXT IMMEDIATE STEPS

### To Complete Phase 3:
1. Verify all test factories are correctly configured
2. Run full test suite: `php artisan test`
3. Debug any failing tests
4. Ensure all 31 tests pass
5. Add remaining model relationships (if any missing)

### For Phase 4 (API & Testing):
1. Document API endpoints with examples
2. Create Postman/Insomnia collection
3. Test all CRUD operations
4. Test edge cases (stock validation, etc.)
5. Create API documentation

### For Phase 5 (Frontend):
1. Create product management UI
2. Create purchase/sale forms
3. Create dashboard with alerts
4. Add user management interface
5. Test responsive design

---

## 18. CONTACT & REPOSITORY INFO

- **Repository:** https://github.com/PhilipLee2002/fraca-inventory-system
- **Branch:** main
- **Local Path:** C:\xampp\htdocs\InventorySystem
- **Environment:** XAMPP on Windows
- **Laravel Version:** 12.x
- **PHP Version:** 8.1+

---

**Document Last Updated:** February 17, 2026  
**Next Review Date:** After Phase 3 completion (Feb 18-19, 2026)

