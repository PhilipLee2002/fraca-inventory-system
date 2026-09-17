# Test Plan Assumptions & Schema Documentation

**FRACA SERVCOM Inventory Management System**  
**System Under Test (SUT): Laravel 12 Backend API + Database**  
**Document Date:** September 2026

---

## 1. Database Schema Assumptions

All assumptions documented below are based on analysis of migrations and models in `/database/migrations/` and `/app/Models/`.

### 1.1 Core Tables & Columns

| Table | Key Columns | Purpose |
|-------|------------|---------|
| **users** | id, name, email, password, role_id, email_verified_at, created_at, updated_at | User authentication & identification |
| **roles** | id, name, description | Admin, Manager, Staff |
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

All JSON endpoints are prefixed with `/api` (not `/api/v1`). The Blade app uses **session cookies** on the `web` middleware group. `POST /api/login` may also return a Sanctum token; UI calls do not send Bearer tokens. Every authenticated API route has a `permission:*` middleware.

### 2.1 Routes Defined

See [docs/system-architecture.md](system-architecture.md) §6.1. There is no `/api/v1` prefix. Extra launch fields: `sales.reference_number` (M-Pesa), `GET /sales/{id}/invoice` (PDF, web).

### 2.2 Authentication Assumptions

- Session auth for the shop UI
- Login JSON may include a token for optional API clients
- Each user has one role: Admin, Manager, or Staff
- Public registration is disabled
- Inactive users cannot log in

---

## 3. Business Logic Assumptions

### 3.1 Stock Management

- When a **Purchase** is received, `product.current_stock` is incremented
- When a **Sale** is **completed**, `product.current_stock` is decremented. Pending sales do not touch stock
- M-Pesa (`payment_method=transfer`) requires `reference_number`
- Staff API payloads hide `cost_price`
- Website `/api/contact` never creates an IMS sale

### 3.2 Alerts

- **Low-stock alerts** are triggered when `product.current_stock < product.stock_threshold`
- Alerts are stored in `alerts` table with status (unresolved/resolved)
- Alerts can be resolved manually or via dashboard action

### 3.3 Transaction Integrity

- Purchase/Sale creation is **atomic** (all-or-nothing): either entire purchase+items+stock update succeed or all fail
- Stock updates are logged in `stock_histories` with type (`purchase`, `sale`, `adjustment`)

### 3.4 User Roles & Permissions

- **Admin:** Full access (Benjamin)
- **Manager:** Reports, stock, purchases; sees cost (Anne, Franklin)
- **Staff:** Create customers/sales only; no cost, no reports (Receptionist)

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
| benjamin@fracaservcomltd.co.ke | FRACASERVCOM_STAFF_PASSWORD | Admin | Seeded MD |
| anne@fracaservcomltd.co.ke | same | Manager | Seeded accountant |
| factory users in tests | password | various | PHPUnit |

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

- Frontend UI is in scope (manual + launch feature tests)
- Barcode scanner, eTIMS, live website stock: out of launch
- Email notification delivery: later

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

- [x] API paths are `/api/...` not `/api/v1`
- [x] Shop UI uses session auth, not Bearer-only Sanctum
- [x] Roles are Admin / Manager / Staff; Staff do not get view-report
- [x] Catalog comes from the website extract (furniture + bags, no hardware)

---

**Document Status:** Complete  
**Last Updated:** September 2026  
**Approved By:** Development Team  
**Next Review:** Post-implementation (before UAT)
