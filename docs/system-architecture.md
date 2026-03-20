# System Architecture Document
## FRACA SERVCOM Inventory Management System

**Document Version:** 1.0
**Date:** March 2026
**Technology Stack:** Laravel 11, PHP 8.2, MySQL, Bootstrap 5, Vanilla JavaScript (ES6+), Vite

---

## 1. System Overview

The FRACA SERVCOM Inventory Management System is a monolithic web application built on the Laravel framework. It follows a hybrid architecture: server-rendered Blade page shells with AJAX-driven content loading via a JSON REST API. All business logic resides on the server; the browser handles rendering and user interaction through vanilla JavaScript ES6 class modules.

---

## 2. High-Level Architecture Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                          CLIENT (Browser)                         │
│                                                                    │
│  ┌─────────────────┐   ┌──────────────────┐   ┌───────────────┐  │
│  │  Bootstrap 5 UI  │   │  JS Page Modules  │   │  Axios Client │  │
│  │  (HTML/CSS)      │   │  (ES6 Classes)    │   │  (api/client) │  │
│  └────────┬─────────┘   └────────┬──────────┘   └──────┬────────┘  │
│           │                      │                      │           │
└───────────┼──────────────────────┼──────────────────────┼───────────┘
            │  Page Load (HTML)    │  AJAX (JSON)         │
            │  ◄────────────────── │ ─────────────────────►
┌───────────▼──────────────────────▼──────────────────────▼───────────┐
│                        LARAVEL APPLICATION                           │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │                      HTTP Kernel                              │   │
│  │  web middleware group: session, CSRF, auth, role, permission  │   │
│  └──────────────────────────────────────────────────────────────┘   │
│                                                                      │
│  ┌─────────────────────┐    ┌──────────────────────────────────┐    │
│  │   routes/web.php     │    │         routes/api.php            │    │
│  │   Blade page shells  │    │   JSON REST API (auth middleware) │    │
│  └──────────┬──────────┘    └──────────────┬───────────────────┘    │
│             │                               │                        │
│  ┌──────────▼──────────┐    ┌──────────────▼───────────────────┐    │
│  │   Web Controllers    │    │         API Controllers           │    │
│  │   (ProfileController │    │  ProductController, SaleController│    │
│  │    UserController)   │    │  PurchaseController, etc.         │    │
│  └──────────┬──────────┘    └──────────────┬───────────────────┘    │
│             │                               │                        │
│  ┌──────────▼───────────────────────────────▼───────────────────┐   │
│  │                     Eloquent ORM (Models)                      │   │
│  │  User, Role, Permission, Product, Category, Supplier,          │   │
│  │  Customer, Sale, SaleItem, Purchase, PurchaseItem,             │   │
│  │  StockHistory, Alert                                           │   │
│  └──────────────────────────────┬───────────────────────────────┘   │
│                                  │                                   │
└──────────────────────────────────┼───────────────────────────────────┘
                                   │ PDO / MySQL Driver
┌──────────────────────────────────▼───────────────────────────────────┐
│                     MySQL Database: fraca_inventory                   │
│                                                                       │
│  users  roles  permissions  role_permission                           │
│  products  categories  suppliers  customers                           │
│  sales  sale_items  purchases  purchase_items                         │
│  stock_histories  alerts  cache  jobs                                 │
└───────────────────────────────────────────────────────────────────────┘
```

---

## 3. Component Descriptions

### 3.1 Frontend Layer

**Bootstrap 5 UI**
Provides the responsive grid, modal system, form controls, badges, and utility classes. No jQuery dependency. All interactive components (modals, tooltips, popovers) are initialized via the Bootstrap JavaScript API exposed on `window.bootstrap`.

**JavaScript Page Modules**
Each page has a dedicated ES6 class module (`resources/js/modules/*.js`). Modules are lazy-loaded by `app.js` based on the `body[data-page]` attribute set by the Blade layout. Each module follows the same lifecycle: `init()` → `bindEvents()` → `loadData()` → `renderTable()`.

**Axios API Client (`resources/js/api/client.js`)**
A configured Axios instance that:
- Attaches `X-CSRF-TOKEN` from `window.appData.csrfToken` to every request
- Shows/hides a global loading indicator on request start/end
- Intercepts 401 responses and redirects to `/login`
- Intercepts 403 responses and shows a permission-denied toast

### 3.2 Application Layer

**HTTP Kernel (`app/Http/Kernel.php`)**
Registers middleware groups. The `web` group handles session, CSRF, and authentication. Custom middleware `CheckRole` and `CheckPermission` are registered as route middleware aliases.

**Web Routes (`routes/web.php`)**
Serve Blade page shells. Protected by `auth` middleware and per-route `permission:*` middleware. No data is returned from web routes — they only render the HTML shell.

**API Routes (`routes/api.php`)**
Registered under the `/api` prefix with the `web` middleware group (session-based auth). All routes except `POST /api/login` require the `auth` middleware. Returns JSON responses only.

**API Controllers (`app/Http/Controllers/Api/`)**
One controller per resource. All extend `BaseController` which provides standardized response helpers: `sendSuccess()`, `sendPaginated()`, `sendCreated()`, `sendUpdated()`, `sendDeleted()`, `sendError()`.

**Middleware**
- `CheckRole` — validates `auth()->user()->role->name` against allowed roles
- `CheckPermission` — validates that the user's role has the required permission name

### 3.3 Data Layer

**Eloquent Models**
All models use Laravel's Eloquent ORM. Key relationships:

```
User         belongsTo  Role
Role         belongsToMany  Permission  (via role_permission pivot)
Product      belongsTo  Category
Product      belongsTo  Supplier
Product      hasMany    StockHistory
Sale         belongsTo  Customer
Sale         belongsTo  User
Sale         hasMany    SaleItem
SaleItem     belongsTo  Product
Purchase     belongsTo  Supplier
Purchase     belongsTo  User
Purchase     hasMany    PurchaseItem
PurchaseItem belongsTo  Product
StockHistory belongsTo  Product (polymorphic source)
Alert        belongsTo  Product
```

**Database: MySQL (`fraca_inventory`)**
Relational database with foreign key constraints enforcing referential integrity. Migrations are version-controlled in `database/migrations/`.

---

## 4. Authentication and Authorization Architecture

### 4.1 Authentication Flow

```
Browser                    Laravel
  │                           │
  ├── POST /api/login ────────►│
  │   { email, password }      │  AuthController::login()
  │                            │  Auth::attempt()
  │◄── 200 { user, role } ─────┤  Session started
  │                            │
  ├── GET /products ───────────►│
  │   (session cookie)         │  auth middleware → CheckPermission
  │◄── 200 HTML shell ─────────┤
  │                            │
  ├── GET /api/products ───────►│
  │   (session cookie)         │  auth middleware → ProductController
  │◄── 200 { data, pagination }┤
```

### 4.2 Permission Resolution

```
Role (admin/manager/staff)
    └── rolePermissions() [many-to-many via role_permission]
            └── Permission (name: 'delete-product', 'view-sale', etc.)

Blade layout injects:
    window.appData.permissions = ['view-product', 'create-sale', ...]

JS utility:
    window.utils.hasPermission('delete-product')
    → checks window.appData.permissions.includes('delete-product')
```

### 4.3 Admin Verification Flow (Manager Deletes)

```
Manager clicks Delete
    → JS checks getUserRole() === 'manager'
    → showAdminVerifyModal() opens Bootstrap modal
    → Manager enters admin email + password
    → POST /api/verify-admin { email, password }
    → AuthController::verifyAdmin() checks credentials
    → Returns { verified: true/false }
    → If verified: proceed with DELETE /api/resource/{id}
    → If not: show error toast, abort
```

---

## 5. Stock Management Architecture

### 5.1 Stock Change Events

| Event | Trigger | Stock Effect |
|-------|---------|-------------|
| Purchase created (status: received) | `PurchaseController::store()` | +quantity per item |
| Purchase status → received | `PurchaseController::updateStatus()` | +quantity per item |
| Sale created (status: completed) | `SaleController::store()` | -quantity per item |
| Sale status → completed | `SaleController::updateStatus()` | -quantity per item |
| Manual adjustment | `StockAdjustmentController::store()` | ±quantity or set |

### 5.2 Stock History Tracking

Every stock change writes a `stock_histories` record:

```
stock_histories
├── product_id
├── transaction_type  (purchase / sale / adjustment)
├── quantity_change   (positive = in, negative = out)
├── previous_stock
├── new_stock
├── reference_id      (sale_id or purchase_id)
├── reference_type    (App\Models\Sale or App\Models\Purchase)
├── reason            (for manual adjustments)
└── user_id
```

### 5.3 Alert Generation

The `GenerateStockAlerts` console command (scheduled via `app/console/Kernel.php`) queries all products where `current_stock <= reorder_level` and creates or updates `alerts` records. The dashboard fetches these via `GET /api/alerts`.

---

## 6. API Architecture

### 6.1 URL Structure

```
/api/login                          POST  — public
/api/logout                         POST  — auth required
/api/products                       GET, POST
/api/products/{id}                  GET, PUT, DELETE
/api/products/low-stock             GET
/api/categories                     GET, POST, PUT, DELETE
/api/suppliers                      GET, POST, PUT, DELETE
/api/customers                      GET, POST, PUT, DELETE
/api/sales                          GET, POST
/api/sales/{id}                     GET, DELETE
/api/sales/{id}/status              PUT
/api/purchases                      GET, POST
/api/purchases/{id}                 GET, DELETE
/api/purchases/{id}/status          PUT
/api/stock-adjustments              GET, POST
/api/reports/dashboard              GET
/api/reports/sales                  GET
/api/reports/purchases              GET
/api/reports/stock-levels           GET
/api/reports/inventory-valuation    GET
/api/reports/profit-loss            GET
/api/reports/stock-movement         GET
/api/users                          GET, POST, PUT, DELETE
/api/users/roles                    GET
/api/verify-admin                   POST
/api/recent-activity                GET
/api/alerts                         GET
/api/alerts/{id}/read               PATCH
```

### 6.2 Standard Response Format

```json
{
  "success": true | false,
  "message": "Human-readable message",
  "data": { } | [ ] | null,
  "errors": { } | null
}
```

Paginated responses wrap data in:
```json
{
  "data": {
    "data": [...records],
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
```

---

## 7. Frontend Build Architecture

### 7.1 Vite Configuration

Vite processes `resources/css/app.css` and `resources/js/app.js` as entry points. Page modules are dynamically imported (code-split) so each page only loads the JavaScript it needs.

```
public/build/
├── manifest.json
├── assets/app-[hash].css
├── assets/app-[hash].js          ← entry chunk
├── assets/dashboard-[hash].js    ← lazy chunk
├── assets/products-[hash].js     ← lazy chunk
└── ...
```

### 7.2 Global State

```javascript
window.appData = {
    user: { id, name, email, role },
    permissions: ['view-product', 'create-sale', ...],
    csrfToken: '...'
}
window.bootstrap = Bootstrap  // Bootstrap JS namespace
window.apiClient = axios       // Configured Axios instance
window.utils = { showToast, hasPermission, showConfirmModal, ... }
window.formatKES = (value) => 'KSh X,XXX.XX'
```

---

## 8. Deployment Architecture

### 8.1 Development Environment

```
Developer Machine
├── php artisan serve     → http://localhost:8000
├── npm run dev           → Vite HMR on http://localhost:5173
└── MySQL (local)         → fraca_inventory database
```

### 8.2 Production Environment

```
Web Server (Apache / Nginx)
├── Document root: /public
├── PHP-FPM (PHP 8.2)
├── npm run build → compiled assets in /public/build
└── MySQL Server → fraca_inventory database

Cron (for scheduled commands):
* * * * * php artisan schedule:run
```

### 8.3 Environment Configuration (`.env`)

Key variables:

```
APP_ENV=production
APP_KEY=base64:...
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=fraca_inventory
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=database
CACHE_DRIVER=database
```

---

## 9. Directory Structure Summary

```
/
├── app/                    # PHP application code
│   ├── Http/Controllers/   # Web + API controllers
│   ├── Http/Middleware/    # CheckRole, CheckPermission
│   ├── Http/Requests/      # Form validation classes
│   └── Models/             # Eloquent models
├── bootstrap/              # Laravel bootstrap files
├── config/                 # App configuration
├── database/
│   ├── migrations/         # Database schema versions
│   ├── seeders/            # Seed data (roles, permissions, demo data)
│   └── factories/          # Model factories for testing
├── docs/                   # Project documentation
├── public/                 # Web root (index.php, compiled assets)
├── resources/
│   ├── css/app.css         # Bootstrap + custom styles
│   ├── js/                 # Frontend JavaScript
│   │   ├── app.js          # Entry point
│   │   ├── api/client.js   # Axios instance
│   │   ├── modules/        # Page modules
│   │   └── utils/          # Shared utilities
│   └── views/              # Blade templates
│       ├── layouts/        # app.blade.php (main layout)
│       ├── partials/       # navbar, footer, modals, toasts
│       ├── dashboard/
│       ├── products/
│       ├── sales/
│       ├── purchases/
│       ├── categories/
│       ├── suppliers/
│       ├── customers/
│       ├── stock-adjustments/
│       ├── reports/
│       └── users/
├── routes/
│   ├── web.php             # Page routes (Blade)
│   └── api.php             # API routes (JSON)
└── tests/
    ├── Unit/               # Business logic tests
    └── Feature/            # HTTP endpoint tests
```
