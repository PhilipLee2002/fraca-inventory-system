# FRACA SERVCOM — Development Reference Document

> This document lists **all system features** (collected from the SRS/SDS/proposal notes) and a **phased task list** for development. Paste this into the development chat to use as the authoritative task reference.

---

## Project Context

* **Project:** FRACA SERVCOM Inventory Management System
* **Tech stack (backend):** PHP (Laravel), MySQL
* **Development environment:** XAMPP / Local dev (or Laravel Sail/Valet if preferred)
* **Client:** FRACA SERVCOM (furniture & hardware supplies)
* **Primary goals:** Simple, affordable, reliable web-based inventory system for a medium-sized SME. Automate stock tracking, low-stock alerts, basic reporting, and user roles.

---

## 1. Features (full list)

These features are derived from the proposal, SRS drafts, and SDS.

### Core Inventory & Product Features

* Product CRUD (Create, Read, Update, Delete) with validations
* Product categories and category management
* Barcode field and barcode uniqueness checks
* Barcode generation/printing (optional)
* Product search, filtering, and sorting
* Product detail page with stock, price, supplier info

### Stock & Stock Control

* Stock quantity field and stock threshold per product
* Manual stock adjustments (corrections/audits)
* Automatic stock updates on purchases and sales
* Stock history log (audit trail for all stock movements)
* Stock reconciliation tools (import physical count, adjust)

### Sales & Purchase Management

* Purchase orders / Purchase recording
* Purchase line items (items, qty, unit price, subtotal)
* Sales invoices / Sales recording
* Sales line items (items, qty, unit price, subtotal)
* Support for customers and suppliers master data
* Auto-update stock when transactions are recorded

### Alerts & Notifications

* Low-stock detection (product stock < threshold)
* Dashboard visual alerts for low stock
* Alert history and resolution tracking
* Optional email notifications for critical alerts

### Users, Roles & Security

* Authentication (login/logout), password hashing
* Role-Based Access Control (Admin, Staff)
* User management (create, update, deactivate users)
* Session management and permissions checks
* Input sanitization and CSRF protection

### Reporting & Exports

* Stock report (current stock, below-threshold list)
* Sales reports (by day, product, customer, period)
* Purchase reports (by supplier, product, period)
* Export reports as CSV and PDF
* Dashboard summary widgets (totals, trends)

### Integrations & Utilities

* Barcode scanner support (works with USB/phone camera via browser)
* Import products or transactions via CSV
* Backup and restore guidance (database dump)
* Responsive UI for desktop and tablet (mobile-friendly)

### UX / Admin Features

* Clean dashboard with quick actions
* Pagination for large tables
* Inline edit where appropriate
* Audit logs for user actions
* Basic settings page (business name, currency, tax if needed)

---

## 2. Development Phases & Task List

Below are the recommended development phases and tasks. The backend is the priority; after core backend modules are stable and tested, frontend work will be implemented and refined.

### Phase 0 — Project Setup & Planning

**Goal:** Prepare environment, repo, and plan.

* Initialize Git repository and branching strategy (main/dev/feature)
* Create README and contribution guide
* Set up Laravel project (composer create-project) or confirm XAMPP setup
* Create .env example and DB connection guidance
* Install required packages (auth scaffolding, PDF, CSV, image handling)
* Define coding standards and linting (PHP CS Fixer / PHPCS)
* Create project issue tracker / kanban board

**Deliverables:** Repo with initial Laravel project, README, board created.

---

### Phase 1 — Database & Core Models (Backend)

**Goal:** Implement DB schema, Eloquent models, and migrations.

* Translate SQL DDL into Laravel migrations
* Create Eloquent models: Product, Category, User, Role, Supplier, Customer, Purchase, PurchaseItem, Sale, SaleItem, StockHistory, Alert
* Define model relationships (hasMany, belongsTo)
* Seeders for roles and test data
* Implement database constraints and indexes (barcode unique, foreign keys)

**Deliverables:** Migrations, models, seeders, passing migrations on local DB.

---

### Phase 2 — Authentication & Authorization

**Goal:** User system and RBAC.

* Implement Laravel authentication (Laravel Breeze/Jetstream or custom)
* Roles & permissions middleware (basic Role check)
* User CRUD endpoints with validation
* Login/logout flows and session management

**Deliverables:** Working auth, admin user creation, role checks in middleware.

---

### Phase 3 — Core Backend Business Logic

**Goal:** Implement core transaction logic without UI (API/controllers + unit tests)

* Product CRUD controllers and validation
* Supplier and Customer CRUD
* Purchase creation endpoint: creates purchase + purchase_items, updates product stock, writes stock_history
* Sale creation endpoint: validates stock, creates sale + sale_items, decreases product stock, writes stock_history
* Stock adjustment endpoint for manual corrections (creates StockHistory entry)
* Alerts generator (scheduled command or on-demand check) to flag low-stock products
* Reporting endpoints (JSON) for stock, sales, purchases

**Deliverables:** Controllers, services (if using), unit tests for critical flows (sale/purchase/stock update).

---

### Phase 4 — API, Validation & Testing

**Goal:** Harden backend, add APIs for frontend.

* Create API routes (versioning if needed)
* Add request validation classes (FormRequests)
* Implement JSON responses and consistent error handling
* Write unit and feature tests for: auth, product CRUD, purchase flow, sale flow, stock_history
* Implement basic API documentation (OpenAPI / Postman collection)

**Deliverables:** Test suite with passing tests, Postman collection or OpenAPI spec.

---

### Phase 5 — Frontend (Priority after backend core complete)

**Goal:** Build the UI that consumes backend APIs. Frontend will be prioritized after core backend is stable and well-tested.

* Decide frontend approach: Blade templates (Laravel) or SPA (Vue/React)
* Implement layout and global components (header, sidebar, footer, auth views)
* Dashboard: summary widgets and low-stock list
* Product pages: list, create/edit forms, detail view, import CSV
* Purchase pages: create purchase (line items UI), list, view
* Sales pages: create sale (scan barcode, POS-like entry), list, view
* Stock history and adjustments UI
* Reports page: filters and export buttons (CSV/PDF)
* User management pages and role assignment
* Responsive behavior and basic accessibility checks
* Barcode scan integration for sale entry (use JavaScript Web APIs or libraries)

**Deliverables:** Usable UI connected to backend APIs, forms validated, exports working.

---

### Phase 6 — Notifications, Background Jobs & Extras

**Goal:** Add useful automation and polishing.

* Implement email notifications for critical alerts (configurable)
* Create scheduled jobs (Laravel Scheduler) for daily low-stock checks and report generation
* Implement logs (Monolog) and basic monitoring hooks
* Add Import/Export utilities (CSV import for products, export reports)

**Deliverables:** Cron-friendly schedule, email templates, import script.

---

### Phase 7 — Testing, QA & Bug Fixing

**Goal:** Stabilize system, fix bugs, and prepare for handover.

* Full QA pass of all flows (sales, purchases, stock adjustments)
* Fix validation, edge cases (negative stock, concurrent updates)
* Usability adjustments to frontend
* Prepare test cases for UAT with the client

**Deliverables:** QA report, bug tracker cleared for release.

---

### Phase 8 — Deployment & Handover

**Goal:** Deploy to production environment and hand over documentation.

* Prepare deployment checklist (DB backup, migrations, .env updates)
* Provide installation & setup guide (XAMPP or live server steps)
* Provide user manual and developer README
* Handover meeting and training session with client

**Deliverables:** Deployed system, documentation, training session completed.

---

### Phase 9 — Post-Delivery Maintenance

**Goal:** Define maintenance tasks and minor improvements.

* Bug-fix window & small improvements list
* Backup schedule and maintenance plan
* Optional future features backlog (multi-branch, advanced analytics, mobile app)

**Deliverables:** Maintenance plan and backlog.

---

## 3. Acceptance Criteria (for major features)

* Product CRUD: pass validation, persisted correctly, barcode uniqueness enforced
* Sales: cannot create sale if any item has insufficient stock; stock_history created
* Purchases: stock increases and stock_history created
* Low-stock alerts: detected reliably and visible on dashboard
* Reports: exportable to CSV and PDF and match raw data
* Security: authentication and role checks in every protected route

---

## 4. Recommended Development Practices

* Use migrations and seeders for schema and initial data
* Keep business logic in Services where possible (thin controllers)
* Write tests for transactions that affect stock
* Use feature flags for risky changes
* Run DB backups before major migrations

---

## 5. Developer Handoff Snippet (copy-paste summary)

> FRACA SERVCOM IMS — backend-first development in PHP (Laravel). Core modules: Products, Stock, Sales, Purchases, Users/Roles, Alerts, Reports. Follow phases: Project setup → DB & Models → Auth → Core business logic → API & tests → Frontend → Notifications & Jobs → QA → Deployment → Maintenance. Ensure stock_history logging and stock threshold alerts are implemented early; build frontend after core backend is stable.

