# Implementation Strategy Document
## FRACA SERVCOM Inventory Management System

**Document Version:** 1.0
**Date:** March 2026
**Author:** Development Team
**Technology Stack:** Laravel 11, PHP 8.2, MySQL, Bootstrap 5, Vanilla JavaScript (ES6+), Vite

---

## 1. Project Overview

The FRACA SERVCOM Inventory Management System is a web-based application designed to manage products, stock levels, sales, purchases, suppliers, customers, and reporting for a small-to-medium business. The system enforces role-based access control (RBAC) across three user tiers: Admin, Manager, and Staff.

### 1.1 Goals

- Provide a single-page-like experience using AJAX-driven modals without full page reloads
- Enforce granular permission checks at both the API and UI layers
- Maintain accurate stock levels through automated tracking on every sale and purchase
- Deliver actionable reporting with CSV export capability
- Support a clean, responsive UI using Bootstrap 5

---

## 2. Technology Stack Decisions

| Layer | Technology | Rationale |
|-------|-----------|-----------|
| Backend Framework | Laravel 11 (PHP 8.2) | Mature ecosystem, Eloquent ORM, built-in auth scaffolding, artisan CLI |
| Database | MySQL (`fraca_inventory`) | Relational integrity for stock/sales/purchase transactions |
| Frontend Build | Vite | Fast HMR, native ES module support, replaces Laravel Mix |
| CSS Framework | Bootstrap 5 | Responsive grid, modal system, utility classes — no jQuery dependency |
| JavaScript | Vanilla ES6+ (class-based modules) | No framework overhead; full control over DOM and API calls |
| HTTP Client | Axios (via `resources/js/api/client.js`) | Interceptors for CSRF, auth errors, and loading indicators |
| Icons | Font Awesome 6 (CDN) | Comprehensive icon set, no build step required |
| Authentication | Laravel Breeze (session-based) | Simple, secure, integrates with middleware stack |
| Testing | PHPUnit 11 (via Laravel) | Native Laravel test runner, supports feature and unit tests |

### 2.1 Why Session Auth Instead of Sanctum Tokens

The application is a traditional server-rendered SPA hybrid. All pages are served by Laravel Blade, and JavaScript modules make AJAX calls within the same session context. Using session-based `auth` middleware (rather than `auth:sanctum` with token headers) avoids the complexity of token management while maintaining full CSRF protection via the `X-CSRF-TOKEN` header injected by Axios.

---

## 3. Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                     Browser (Client)                     │
│  Bootstrap 5 UI  │  Vanilla JS Modules  │  Axios Client  │
└──────────────────────────┬──────────────────────────────┘
                           │ AJAX (JSON) + Session Cookie
┌──────────────────────────▼──────────────────────────────┐
│                    Laravel Application                    │
│                                                          │
│  routes/web.php   →  Blade Views (page shell)           │
│  routes/api.php   →  API Controllers (JSON responses)   │
│                                                          │
│  Middleware Stack:                                       │
│    web → auth → CheckRole → CheckPermission             │
│                                                          │
│  Models (Eloquent ORM)                                   │
│    Product, Sale, Purchase, Category, Supplier,          │
│    Customer, User, Role, Permission, StockHistory        │
└──────────────────────────┬──────────────────────────────┘
                           │ PDO
┌──────────────────────────▼──────────────────────────────┐
│                MySQL — fraca_inventory                    │
└─────────────────────────────────────────────────────────┘
```

### 3.1 Request Flow

1. User navigates to a page (e.g., `/products`) — Laravel serves a Blade shell view
2. `body[data-page]` attribute is set from the route name (e.g., `products`)
3. `app.js` reads `data-page` and dynamically imports the matching JS module
4. The module calls the API (e.g., `GET /api/products`) via Axios
5. The API controller queries the database and returns a JSON response
6. The JS module renders the response into the DOM (table rows, modals, etc.)

---

## 4. Module Structure

### 4.1 Backend (`app/`)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # JSON API controllers (one per resource)
│   │   │   ├── BaseController.php     # Shared response helpers
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── SaleController.php
│   │   │   ├── PurchaseController.php
│   │   │   ├── StockAdjustmentController.php
│   │   │   ├── ReportController.php
│   │   │   └── UserController.php
│   │   └── (Web controllers for profile/user management)
│   ├── Middleware/
│   │   ├── CheckRole.php       # Validates user role
│   │   └── CheckPermission.php # Validates specific permission
│   └── Requests/               # Form request validation classes
├── Models/
│   ├── User.php, Role.php, Permission.php
│   ├── Product.php, Category.php
│   ├── Sale.php, SaleItem.php
│   ├── Purchase.php, PurchaseItem.php
│   ├── Supplier.php, Customer.php
│   ├── StockHistory.php, Alert.php
└── Providers/
    └── AppServiceProvider.php
```

### 4.2 Frontend (`resources/js/`)

```
resources/js/
├── app.js                  # Entry point — bootstraps Bootstrap, utils, lazy-loads modules
├── api/
│   └── client.js           # Axios instance with CSRF + auth interceptors
├── modules/                # One class per page
│   ├── dashboard.js
│   ├── products.js
│   ├── categories.js
│   ├── suppliers.js
│   ├── customers.js
│   ├── sales.js
│   ├── purchases.js
│   ├── stock-adjustments.js
│   ├── reports.js
│   └── users.js
└── utils/
    ├── permissions.js      # hasPermission(), hasRole(), getUserRole()
    ├── modal.js            # showConfirmModal(), showAdminVerifyModal()
    ├── toast.js            # showToast()
    ├── validation.js       # displayValidationErrors(), clearValidationErrors()
    ├── export.js           # exportToCSV()
    └── cache.js            # cachedFetch() — in-memory cache for filter dropdowns
```

---

## 5. Role-Based Access Control (RBAC) Design

### 5.1 Roles

| Role | Description |
|------|-------------|
| Admin | Full system access including hard deletes and user management |
| Manager | Full access except hard deletes; deletes require admin verification |
| Staff | Read-only + create sales and purchases only |

### 5.2 Permission Matrix

| Permission | Admin | Manager | Staff |
|-----------|-------|---------|-------|
| view-product / view-category / view-supplier / view-customer | ✓ | ✓ | ✓ |
| create-product / edit-product | ✓ | ✓ | ✗ |
| delete-product / delete-category / delete-sale / delete-purchase | ✓ | ✗* | ✗ |
| create-sale / create-purchase | ✓ | ✓ | ✓ |
| edit-sale / edit-purchase | ✓ | ✓ | ✗ |
| manage-stock | ✓ | ✓ | ✗ |
| view-report / export-report | ✓ | ✓ | ✓ |
| view-user / create-user / edit-user / delete-user | ✓ | ✗ | ✗ |

*Manager delete operations trigger an Admin Verification Modal requiring admin credentials.

### 5.3 Enforcement Layers

- **Route level:** `middleware('permission:view-product')` on web routes
- **API level:** Controllers check `auth()->user()->role->rolePermissions` (implicit via middleware)
- **UI level:** `window.utils.hasPermission()` / `hasAnyPermission()` controls button visibility
- **Admin verify:** `POST /api/verify-admin` validates admin credentials before destructive operations

### 5.4 Data Flow for Permissions

```
window.appData.permissions  ←  injected by Blade layout (app.blade.php)
        ↓
window.utils.hasPermission('delete-product')
        ↓
Conditionally renders delete button in JS module renderTable()
```

---

## 6. API Design Patterns

### 6.1 Response Envelope

All API responses follow a consistent envelope via `BaseController`:

```json
// Success (list with pagination)
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "data": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 98,
      "from": 1,
      "to": 20
    }
  }
}

// Success (single resource)
{ "success": true, "message": "...", "data": { ...resource } }

// Error
{ "success": false, "message": "...", "errors": {} }
```

### 6.2 Pagination Strategy

- Default: 20 records per page
- Maximum: 100 records per page (enforced in controllers)
- Dropdowns (categories, suppliers, customers for forms): fetched without `per_page` to return flat arrays
- The frontend `extract()` helper in `products.js` handles both paginated and flat response shapes

### 6.3 Stock Management

Stock changes are tracked via `StockHistory` on every:
- Purchase creation/update → stock incremented
- Sale creation/update → stock decremented
- Manual stock adjustment → stock set to new value with reason logged

Low-stock alerts are generated by the `GenerateStockAlerts` console command (scheduled).

---

## 7. Database Design

### 7.1 Key Tables

| Table | Purpose |
|-------|---------|
| `users` | System users with `role_id` FK |
| `roles` | Admin, Manager, Staff |
| `permissions` | Granular permission strings (e.g., `delete-product`) |
| `role_permission` | Pivot: role ↔ permission |
| `products` | SKU, name, cost/selling price, stock levels, reorder level |
| `categories` | Product groupings |
| `suppliers` | Vendor information |
| `customers` | Customer records |
| `sales` | Sale header (invoice number, customer, date, status) |
| `sale_items` | Line items (product, qty, unit price, subtotal) |
| `purchases` | Purchase header (PO number, supplier, date, status) |
| `purchase_items` | Line items (product, qty, cost price, subtotal) |
| `stock_histories` | Polymorphic audit trail of all stock changes |
| `alerts` | Low-stock and system notifications |

### 7.2 Stock Integrity

- `products.current_stock` is the authoritative stock value
- Every mutation writes a `stock_histories` record with `transaction_type`, `quantity_change`, `previous_stock`, `new_stock`
- Reorder level triggers alert generation via scheduled command

---

## 8. Frontend Implementation Strategy

### 8.1 Module Initialization Pattern

Each page module follows the same lifecycle:

```javascript
class ProductsModule {
    init() {
        this.bindEvents();      // Attach DOM event listeners
        this.loadFilters();     // Populate dropdowns (async)
        this.loadProducts();    // Fetch and render table (async)
    }
}
```

### 8.2 Modal Strategy

- All CRUD operations use Bootstrap 5 modals (no page navigation)
- Modals are defined in Blade views and toggled via `new bootstrap.Modal(...).show()`
- `window.bootstrap` is set globally in `app.js` so all modules can access it
- View modals (read-only) hide the form and show a rendered HTML summary

### 8.3 Error Handling

- 422 Validation errors: displayed inline via `displayValidationErrors()`
- 401 Unauthorized: Axios interceptor redirects to `/login`
- 403 Forbidden: Axios interceptor shows a toast notification
- Network errors: caught in module `catch` blocks with toast feedback

### 8.4 Performance Considerations

- Filter dropdowns use `cachedFetch()` to avoid redundant API calls within a session
- Product/supplier/customer lists for forms are fetched once and reused
- Dashboard auto-refreshes every 30 seconds (clears on page unload)
- Vite code-splitting: each page module is a separate chunk loaded on demand

---

## 9. Build and Deployment

### 9.1 Development

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Start servers
php artisan serve          # Backend: http://localhost:8000
npm run dev                # Vite HMR: http://localhost:5173
```

### 9.2 Production Build

```bash
npm run build              # Compiles and hashes assets to public/build/
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9.3 Scheduled Tasks

```bash
# Add to server crontab
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

The `GenerateStockAlerts` command runs on schedule to check products below reorder level and create alert records.

---

## 10. Testing Strategy

See `docs/test-plan.md` for the full test plan. Summary:

- **Unit tests** (`tests/Unit/`): Stock logic, purchase calculations — PHPUnit, SQLite in-memory
- **Feature tests** (`tests/Feature/`): API endpoint verification — Laravel HTTP test client
- **Manual tests**: RBAC UI behavior, modal workflows, responsive design

```bash
php artisan test              # Run all tests
php artisan test --coverage   # With coverage report
```

---

## 11. Security Considerations

- CSRF protection on all state-changing requests (Axios sends `X-CSRF-TOKEN` header)
- Session-based authentication — no tokens stored in localStorage
- Permission checks at both route middleware and API controller level
- Input validation via Laravel Form Requests (server-side) and HTML5 attributes (client-side)
- SQL injection prevention via Eloquent ORM parameterized queries
- XSS prevention via Blade `{{ }}` escaping and JS `esc()` helper in all modules
- Admin verification modal prevents unauthorized destructive operations by Manager role
