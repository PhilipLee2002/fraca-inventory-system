# Test Plan Assumptions & Schema Documentation

**FRACA SERVCOM Inventory Management System**  
**System Under Test (SUT): Laravel 12 Backend API + Database**  
**Document Date: February 14, 2026**

---

## 1. Database Schema Assumptions

All assumptions documented below are based on analysis of migrations and models in `/database/migrations/` and `/app/Models/`.

### 1.1 Core Tables & Columns

| Table | Key Columns | Purpose |
|-------|------------|---------|
| **users** | id, name, email, password, role_id, email_verified_at, created_at, updated_at | User authentication & identification |
| **roles** | id, name, description, created_at, updated_at | User roles (Admin, Staff) |
| **permissions** | id, name, description, created_at, updated_at | Granular permissions (view-product, create-sale, etc.) |
| **role_permission** | role_id, permission_id | Many-to-many junction table |
| **products** | id, sku, barcode, product_name, description, cost_price, selling_price, current_stock, stock_threshold, unit, category_id, supplier_id, image_path, created_at, updated_at | Core inventory items |
| **categories** | id, category_name, description, created_at, updated_at | Product categorization |
| **suppliers** | id, supplier_name, contact_person, email, phone, address, city, country, created_at, updated_at | Supplier information |
| **customers** | id, customer_name, email, phone, address, city, country, created_at, updated_at | Customer information |
| **purchases** | id, purchase_order_number, supplier_id, total_amount, payment_method, status, user_id, notes, created_at, updated_at | Purchase orders |
| **purchase_items** | id, purchase_id, product_id, quantity, unit_price, subtotal, created_at, updated_at | Purchase line items |
| **sales** | id, invoice_number, customer_id, total_amount, payment_method, status, user_id, notes, created_at, updated_at | Sales transactions |
| **sale_items** | id, sale_id, product_id, quantity, unit_price, subtotal, created_at, updated_at | Sales line items |
| **stock_adjustments** | id, product_id, old_quantity, new_quantity, adjustment_type, quantity_changed, reason, notes, user_id, created_at, updated_at | Manual stock corrections |
| **stock_histories** | id, product_id, transaction_type, quantity_change, before_quantity, after_quantity, historic_traceable_type, historic_traceable_id, notes, created_at, updated_at | Complete audit trail |
| **alerts** | id, product_id, alert_type, status, created_at, updated_at, resolved_at | Low-stock alert tracking |

### 1.2 Relationships (Eloquent)

- **User** → Role (BelongsTo), Purchases (HasMany), Sales (HasMany)
- **Role** → Users (HasMany), Permissions (BelongsToMany via role_permission)
- **Product** → Category (BelongsTo), Supplier (BelongsTo), PurchaseItems (HasMany), SaleItems (HasMany), StockHistories (HasMany), Alerts (HasMany)
- **Purchase** → Supplier (BelongsTo), User (BelongsTo), PurchaseItems (HasMany)
- **Sale** → Customer (BelongsTo), User (BelongsTo), SaleItems (HasMany)
- **StockHistory** → Product (BelongsTo), polymorphic (historic_traceable)

### 1.3 Constraints & Validations

- **SKU** and **Barcode** are unique across products
- **Email** is unique for users and customers
- **Foreign key constraints** enforce referential integrity (e.g., product_id must exist in products table)
- **Stock quantity** must be ≥ 0 (enforced in validation)
- **Prices** must be positive (enforced in validation)

---

## 2. API Endpoint Assumptions

All endpoints are prefixed with `/api/v1` and require token-based authentication (`Authorization: Bearer {token}`) via Laravel Sanctum.

### 2.1 Routes Defined

| Method | Endpoint | Controller Method | Auth | Notes |
|--------|----------|------------------|------|-------|
| POST | `/api/v1/login` | AuthController@login | Public | Returns access token |
| POST | `/api/v1/logout` | AuthController@logout | Required | Revokes token |
| GET | `/api/v1/products` | ProductController@index | Required | List products with pagination |
| POST | `/api/v1/products` | ProductController@store | Required | Create new product |
| GET | `/api/v1/products/{id}` | ProductController@show | Required | Get product details |
| PUT | `/api/v1/products/{id}` | ProductController@update | Required | Update product |
| DELETE | `/api/v1/products/{id}` | ProductController@destroy | Required | Delete product |
| GET | `/api/v1/suppliers` | SupplierController@index | Required | List suppliers |
| POST | `/api/v1/suppliers` | SupplierController@store | Required | Create supplier |
| GET | `/api/v1/customers` | CustomerController@index | Required | List customers |
| POST | `/api/v1/customers` | CustomerController@store | Required | Create customer |
| POST | `/api/v1/purchases` | PurchaseController@store | Required | Create purchase order |
| GET | `/api/v1/purchases` | PurchaseController@index | Required | List purchases |
| POST | `/api/v1/sales` | SaleController@store | Required | Create sale/invoice |
| GET | `/api/v1/sales` | SaleController@index | Required | List sales |
| POST | `/api/v1/stock-adjustments` | StockAdjustmentController@store | Required | Record manual stock adjustment |
| GET | `/api/v1/reports/sales` | ReportController@sales | Required | Sales report |
| GET | `/api/v1/reports/purchases` | ReportController@purchases | Required | Purchase report |
| GET | `/api/v1/reports/stock-levels` | ReportController@stockLevels | Required | Inventory report |
| GET | `/api/v1/reports/inventory-valuation` | ReportController@inventoryValuation | Required | Stock valuation |

### 2.2 Authentication Assumptions

- Laravel Sanctum (token-based) for API authentication
- Login endpoint returns `access_token` and `token_type` (Bearer)
- Tokens stored in HTTP-only cookies or Authorization header
- Each user belongs to one role (Admin or Staff)

---

## 3. Business Logic Assumptions

### 3.1 Stock Management

- When a **Purchase** is created, `product.current_stock` is automatically incremented
- When a **Sale** is created, `product.current_stock` is automatically decremented (if stock > sale quantity)
- Each stock change is logged in `stock_histories` table
- Manual **Stock Adjustment** records must be created for corrections

### 3.2 Alerts

- **Low-stock alerts** are triggered when `product.current_stock < product.stock_threshold`
- Alerts are stored in `alerts` table with status (unresolved/resolved)
- Alerts can be resolved manually or via dashboard action

### 3.3 Transaction Integrity

- Purchase/Sale creation is **atomic** (all-or-nothing): either entire purchase+items+stock update succeed or all fail
- Stock updates are logged in `stock_histories` with type (`purchase`, `sale`, `adjustment`)

### 3.4 User Roles & Permissions

- **Admin Role:** Full access (create/edit/delete all resources)
- **Staff Role:** Limited access (create sales/purchases, view reports, cannot manage users)
- Permissions enforced via middleware on protected routes

---

## 4. Testing Environment Assumptions

### 4.1 Technology Stack

| Component | Version | Details |
|-----------|---------|---------|
| PHP | 8.2+ | Laravel 12 compatible |
| Laravel | 12+ | Latest stable |
| MySQL/MariaDB | 5.7+ | Relational database |
| PHPUnit | Latest | Unit/integration testing framework |
| Postman | Latest | API testing tool |
| Docker | Optional | For containerized test environments |
| GitHub Actions | Latest | CI/CD pipeline |

### 4.2 Test Database

- Separate test database (e.g., `fraca_inventory_test`)
- SQLite in-memory database for speed (recommended for CI)
- Database transactions rolled back after each test
- Seeders populate test data (users, roles, products, suppliers, customers)

### 4.3 Test User Credentials

| Username | Password | Role | Purpose |
|----------|----------|------|---------|
| admin@test.local | password | Admin | Full system access testing |
| staff@test.local | password | Staff | Limited access testing |
| (generated per test) | (random) | Various | Dynamic test data |

---

## 5. Test Scope Assumptions

### 5.1 Included

- ✅ Unit tests for Service/Model classes
- ✅ Integration tests for API endpoints
- ✅ Database transaction & stock calculation tests
- ✅ Authentication & authorization tests
- ✅ Validation rule tests
- ✅ Error handling (404, 403, 422 responses)

### 5.2 Excluded

- ❌ Frontend UI/UX testing (out of scope for backend-first)
- ❌ Load/stress testing (Phase 7)
- ❌ Barcode scanner hardware integration (Phase 5+)
- ❌ Email notification delivery (Phase 6)
- ❌ PDF/CSV export rendering (Phase 5+)

---

## 6. File Structure Assumptions

```
/tests
  /Unit
    StockServiceTest.php
    SaleServiceTest.php
    PurchaseServiceTest.php
  /Feature
    ProductControllerTest.php
    SaleControllerTest.php
/docs
  test-plan.md
  test-cases.csv
  traceability.csv
  run-tests.md
  assumptions.md
/postman
  FRACA-SERVCOM-API.postman_collection.json
/ci
  /github-actions
    test-and-newman.yml
```

---

## 7. Known Deviations & Clarifications

If during development any of the following differ from actual implementation, update this section:

- [ ] Product table columns differ from listed schema
- [ ] API endpoint paths differ from `/api/v1` prefix
- [ ] Authentication method differs from Laravel Sanctum
- [ ] Database relations differ from documented relationships
- [ ] Role/Permission matrix differs from Admin/Staff assumption

---

**Document Status:** Complete  
**Last Updated:** February 14, 2026  
**Approved By:** Development Team  
**Next Review:** Post-implementation (before UAT)
