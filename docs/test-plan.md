# Software Test Plan

## FRACA SERVCOM Inventory Management System

---

**Document Information**

| Property | Value |
|----------|-------|
| **Project Name** | FRACA SERVCOM Inventory Management System |
| **System Under Test (SUT)** | Laravel 12 Web App (Blade + Bootstrap UI + AJAX) + API + MySQL Database |
| **Prepared By** | QA Development Team |
| **Date** | February 14, 2026 |
| **Version** | 1.0 |
| **Status** | Draft - Ready for Review |
| **Scope** | Full System Testing (Backend + Frontend UI + E2E) |

---

## 1.0 INTRODUCTION

This document describes the comprehensive test strategy, test procedures, and test cases for the FRACA SERVCOM Inventory Management System. The system is a web-based inventory management solution designed for medium-sized businesses specializing in furniture and hardware supplies. This test plan provides a systematic approach to ensure the full system (frontend UI, backend API, and database) meets specified requirements and quality standards before production deployment.

### 1.1 Goals and Objectives

The primary goals of this testing activity are:

1. **Validate Functional Requirements** – Ensure all features (product management, stock control, sales, purchases, reporting, alerts) work as specified in the SRS.

2. **Ensure Data Integrity** – Verify that database transactions are atomic (all-or-nothing), stock calculations are accurate, and audit trails are maintained.

3. **Verify Authentication & Authorization** – Confirm that role-based access control (RBAC) prevents unauthorized operations and enforces proper permission checks.

4. **Identify Critical Bugs** – Detect defects in stock management logic, API responses, and edge cases (overselling, invalid input, transaction failures) before UAT.

5. **Validate Non-Functional Requirements** – Test performance (API response time < 500ms), security (SQL injection prevention), and system reliability.

6. **Establish Quality Baseline** – Document test results, coverage metrics, and defect trends to enable future regression testing.

7. **Enable Deployment Confidence** – Ensure CI/CD pipeline automatically validates code quality on every commit/PR.

### 1.2 Statement of Scope

#### In Scope

The following components and features are included in the testing scope:

**Frontend UI (Phase 5):**
- Blade views render correctly for authenticated/unauthenticated users
- Bootstrap 5 layout responsiveness (desktop/tablet/mobile)
- AJAX flows using Axios (no full page reload for CRUD operations)
- Form validation UX: inline field errors + toast notifications
- Role/permission UX: menu visibility + blocked actions handled gracefully
- Asset compilation/build (Vite) and runtime JS errors

**Database & Models:**
- All 13 Eloquent models (User, Role, Product, Category, Supplier, Customer, Purchase, PurchaseItem, Sale, SaleItem, StockHistory, Alert, StockAdjustment)
- Database schema (18 migrations), constraints, relationships
- Data integrity (foreign keys, unique constraints, cascading deletes)

**API Endpoints (Backend):**
- Authentication: Login, Logout, Token Management
- Products: CRUD operations, search/filter, validation
- Suppliers & Customers: CRUD operations
- Purchases: Create PO, line items, stock updates, total calculation
- Sales: Create invoice, line items, stock validation, total calculation
- Stock Management: Manual adjustments, history logging
- Reports: Sales, Purchases, Stock Levels, Inventory Valuation

**Business Logic:**
- Stock auto-increment on purchase
- Stock auto-decrement on sale (with validation to prevent overselling)
- Low-stock alert generation
- Stock history audit trail (polymorphic tracking)
- Transaction atomicity for financial operations
- Role-based access control (Admin vs Staff)

**Non-Functional Requirements:**
- Performance: Response time, query optimization (N+1 prevention)
- Security: SQL injection prevention, authorization checks, password hashing
- Reliability: Transaction rollback, soft deletes, database connection pooling

**Test Artifacts:**
- Test cases (50+), test scripts, Postman collection, PHPUnit tests, traceability matrix
- CI/CD integration (GitHub Actions workflow)
- Test documentation and test logs

#### Out of Scope

The following items are explicitly excluded from this testing phase (or tested only as smoke checks):

- **Barcode Scanner Hardware** – USB scanner or phone camera integration (Phase 5+)
- **Email Notifications** – Sending actual emails to users (Phase 6, requires SMTP setup)
- **PDF/CSV Export** – Report rendering to file formats (Phase 5+; optional)
- **Load/Stress Testing** – High-volume user testing, database scalability (Phase 7)
- **Mobile App Integration** – iOS/Android clients (future phase)
- **Backup/Disaster Recovery** – Database backup procedures, restoration testing (operational phase)
- **User Acceptance Testing (UAT)** – Client/stakeholder validation (post-Phase 4)

### 1.3 Major Constraints

**Technical Constraints:**
1. **Database Engine Selection** — PHPUnit tests use SQLite in-memory for speed; integration tests may need MySQL depending on transaction requirements.
2. **Authentication Method** — Tests assume Laravel Sanctum (token-based); adjustments needed if authentication method changes.
3. **Schema Dependency** — Test data assumes specific table names, column names, and relationships documented in `/docs/assumptions.md`. Any schema changes require test updates.

**Resource Constraints:**
1. **Testing Environment** — Tests assume XAMPP/Docker local development environment; CI/CD uses Ubuntu Linux in GitHub Actions.
2. **Code Dependencies** — Tests assume Laravel 12+, PHP 8.2+, MySQL 5.7+; version mismatches may cause failures.
3. **Third-Party Services** — Email, SMS, barcode APIs not tested; mock data assumed.

**Schedule Constraints:**
1. **Testing Timeline** — Phase 4 (API & Testing) targets ~10 days; extensive load testing postponed to Phase 7.
2. **Regression Window** — If code changes during testing, full regression suite must re-run (estimated 20 minutes).

**Personnel Constraints:**
1. **Testing Responsibility** — One QA engineer + development team responsible for test execution and maintenance.
2. **Knowledge Requirements** — Testers must understand Laravel framework, Eloquent ORM, SQL, REST APIs, HTTP status codes.

---

## 2.0 TEST PLAN

This section describes the overall testing strategy and project management approach for executing effective tests.

### 2.1 Software (SCI's) to be Tested

**System Components (SCI's):**

1. **Web UI Layer (Blade + Bootstrap + JS)** — Pages in `routes/web.php` + Blade views + Vite-built assets
   - Dashboard, Products, Purchases, Sales, Stock Adjustments, Users/Profile
   - Navigation visibility and route guards (auth, verified, role, permission)
   - AJAX behavior: Axios requests, loading states, inline errors, toasts

2. **API Layer** — All endpoints in `/api/` routes file
   - ProductController, SaleController, PurchaseController, AuthController, ReportController
   - Request validation rules (StoreSaleRequest, StorePurchaseRequest, etc.)
   - Request/response serialization (JSON)

3. **Model Layer** — 13 Eloquent models with relationships and scopes
   - Proper hydration of relationships (belongsTo, hasMany, belongsToMany)
   - Model events (created, saved, deleting)
   - Query optimization (eager loading, indexes)

4. **Database Layer** — 13 tables, migrations, seeders, constraints
   - Table structure, column types, nullability
   - Foreign key relationships
   - Unique and indexed columns

5. **Service/Business Logic Layer** — (Implemented in controllers/services where applicable)
   - StockService, SaleService, PurchaseService, AlertService
   - Transaction handling, atomicity, rollback
   - Calculation and validation logic

6. **Testing Utilities** — Test factories, seeders, fixtures
   - UserFactory, ProductFactory, SaleFactory, etc.
   - Database seeders (RolesTableSeeder, UsersTableSeeder, PermissionSeeder)
   - Test data population

**Exclusions:**
- Web routes (unless testing authentication redirects)
- Blade templates and view logic
- CSS/JavaScript/asset compilation
- Third-party packages (Laravel Breeze scaffolding assumes correct operation)

**Note:** The exclusions above applied to Phase 4 (backend-only). For “system running fully”, Phase 5 adds UI testing and E2E flows to this plan.

---

### 2.2 Testing Strategy

The testing strategy employs a **multi-layer testing pyramid** approach, with emphasis on automated unit and integration tests:

**Test Layers (by percentage):**
- Unit Tests & Service Tests: 55-70%
- Integration & Feature Tests: 20-30%
- System & E2E Tests: 10-15%
- Manual/Exploratory: ~5%

#### 2.2.0 Frontend (UI) Testing Strategy (Phase 5)

**Objective:** Validate that the Blade + Bootstrap interface correctly drives backend workflows via AJAX without full reloads and that UI feedback is clear.

**Approach:**
1. **UI Smoke Tests** (manual): Can load pages after login, navigation works, no JS console errors, assets load.
2. **CRUD UI Tests** (manual + scripted): Products/Purchases/Sales/Adjustments flows using modals/forms and verifying table updates.
3. **Authorization UX Tests:** Staff vs Admin visibility and blocked actions.
4. **Error Handling UX Tests:** Validation errors display inline; server errors show toast; loading states appear.
5. **Responsive Tests:** Key pages verified at common breakpoints (mobile/tablet/desktop).

#### 2.2.1 Unit Testing Strategy

**Objective:** Test individual functions and methods in isolation to ensure correctness of business logic.

**Components Tested:**
- Model methods (relationships, scopes, mutators)
- Service methods (StockService, SaleService, PurchaseService, AlertService)
- Validation rules and custom validators
- Helper functions

**Approach:**
1. Create test doubles (mocks, stubs) for external dependencies (database mocks where applicable)
2. Test happy paths (valid input → expected output)
3. Test edge cases (boundary conditions, null values, empty arrays)
4. Test error conditions (exceptions, validation failures)

**Example Test Cases:**
- `test_stock_incremented_after_purchase()` — Purchase of 5 units increments stock by 5
- `test_overselling_prevented()` — Sale of 10 units when stock is 3 fails with validation error
- `test_low_stock_alert_generated()` — When stock < threshold, alert is created

**Tools:** PHPUnit 10+ with Laravel testing helpers (Factory, RefreshDatabase trait)

**Database Approach:** SQLite in-memory (`:memory:`) for speed; transaction rollback after each test

**Expected Coverage:** 70-80% code coverage of Model and Service layers

#### 2.2.2 Integration Testing Strategy

**Objective:** Test multiple components working together (Controller + Model + Database) to ensure correct data flow and business workflows.

**Components Tested:**
- API endpoint requests end-to-end (request validation → controller logic → model operations → response)
- Database transactions (atomicity of purchase/sale creation)
- Relationship navigation (eager loading, lazy loading)
- Error response handling (validation errors, authorization failures, not found)

**Approach:**
1. Send actual HTTP requests to API endpoints (via Laravel HTTP test client)
2. Verify response status code, response body structure, and database side effects
3. Test authentication/authorization middleware
4. Test transaction rollback on partial failures

**Example Test Cases:**
- `test_authenticated_user_can_access_products()` — GET /api/v1/products with valid token returns 200
- `test_sale_created_and_stock_updated()` — POST /api/v1/sales creates sale, decrements stock, logs history
- `test_overselling_validation()` — POST /api/v1/sales with insufficient stock returns 422 and stock unchanged

**Tools:** Laravel HTTP client (get, post, put, delete methods), assertJson(), assertDatabaseHas()

**Database Approach:** SQLite file or MySQL test database; transaction rollback via RefreshDatabase trait

**Expected Coverage:** All public API endpoints (20+ endpoints), critical business workflows

#### 2.2.3 Validation Testing Strategy

**Objective:** Ensure that validation rules correctly reject invalid input and accept valid input, and error messages are clear.

**Test Areas:**
- Required field validation (missing mandatory fields)
- Data type validation (string vs integer, email format)
- Uniqueness constraints (SKU, barcode, email must be unique)
- Range validation (negative prices, stock > 0)
- Relationship constraint validation (product exists, supplier exists)

**Approach:**
1. Test each validation rule with valid input → expect 201 Created (success cases)
2. Test with invalid input → expect 422 Unprocessable Entity with specific error message
3. Verify error message clarity and language

**Example Test Cases:**
- `test_create_product_requires_sku()` — POST /api/products without SKU returns 422
- `test_create_product_rejects_duplicate_sku()` — POST /api/products with existing SKU returns 422 "The sku has already been taken"
- `test_create_sale_rejects_negative_price()` — Sale item with unit_price=-10 returns 422

**Tools:** Laravel FormRequest validation, validator assertions, assertJsonValidationErrors()

#### 2.2.4 High-Order Testing Strategy

**Objective:** Test system-level quality attributes beyond functional correctness (performance, security, reliability, usability flow).

**Test Categories:**

1. **Security Testing:**
   - SQL Injection: Attempt malicious SQL in query parameters → Verify parameterized queries prevent injection
   - Authorization: Staff user attempts admin-only action → Verify 403 Forbidden response
   - Authentication: Invalid token → Verify 401 Unauthorized
   - Password Security: Verify passwords are hashed (bcrypt), not stored plaintext

2. **Performance Testing:**
   - Response Time: GET /api/products with 1000+ products → Expect < 500ms response
   - N+1 Query Prevention: GET /api/sales with 100 sales → Count SQL queries ≤ 5 (not 100+)
   - Connection Pooling: 50 concurrent requests → Verify DB connection pool used efficiently

3. **Reliability Testing:**
   - Transaction Atomicity: Purchase creation fails mid-process → Verify all changes rolled back (stock unchanged, purchase not created)
   - Soft Deletes: Delete product → Verify soft delete applied, product still accessible with trashed() scope
   - Cascading Deletes: Delete supplier → Verify associated purchases handled correctly (cascade or prevent)

4. **Data Consistency Testing:**
   - Stock History Audit Trail: Each stock change logged with before/after values, transaction type
   - Referential Integrity: Foreign key constraints prevent orphaned records

**Tools:**
- ApacheBench/Locust for load testing
- Database query logging (Laravel debugbar)
- OWASP ZAP for security scanning (optional)

---

### 2.3 Testing Resources and Staffing

**Personnel:**

| Role | Name/Team | Responsibility |
|------|-----------|-----------------|
| **QA Engineer** | TBD | Test plan creation, test case development, test execution, defect logging |
| **Developer** | Development Team | Unit test implementation, code fix, code review |
| **DevOps** | TBD | CI/CD pipeline setup, test environment provisioning, log analysis |
| **Product Owner** | Client | Requirements clarification, test case review, acceptance |

**Estimated Effort:**
- Test Plan & Test Case Creation: 16 hours
- Test Execution (manual + automated): 8 hours/iteration
- Defect Logging & Triage: 4 hours/iteration
- Regression Testing (per code change): 1-2 hours
- **Total Phase 4 (Testing):** ~10 days of effort

**Skills Required:**
- PHP/Laravel framework knowledge
- SQL and database design
- REST API testing (HTTP methods, status codes, JSON)
- Test automation (PHPUnit, Postman)
- Git and GitHub (branching, CI/CD)

---

### 2.4 Test Work Products

Artifacts produced by the testing activity:

| Work Product | Format | Location | Owner |
|--------------|--------|----------|-------|
| Test Plan | Markdown (.md) | `/docs/test-plan.md` | QA |
| Test Cases | CSV (.csv) | `/docs/test-cases.csv` | QA |
| Test Scripts (PHP) | PHPUnit (.php) | `/tests/Unit/`, `/tests/Feature/` | QA/Dev |
| API Test Collection | Postman JSON | `/postman/FRACA-SERVCOM-API.postman_collection.json` | QA |
| Traceability Matrix | CSV (.csv) | `/docs/traceability.csv` | QA |
| Assumptions Document | Markdown (.md) | `/docs/assumptions.md` | QA |
| Test Execution Log | Text/CSV | TBD (per test run) | QA |
| Defect Report | JIRA/GitHub Issues | https://github.com/.../issues | QA/Dev |
| Test Summary Report | HTML/PDF | TBD (automated by CI) | QA |

---

### 2.5 Test Record Keeping

**Test Log Maintenance:**

All test results are recorded using:

1. **PHPUnit Test Output** — Console output captured during `composer test` run
   ```
   vendor/bin/phpunit --log-junit=test-results/phpunit.xml
   ```

2. **CI/CD Pipeline Logs** — GitHub Actions automatically logs:
   - Job status (pass/fail)
   - Test execution duration
   - Code coverage percentage
   - Links to artifacts

3. **Manual Test Log** — For any manual testing (e.g., Postman exploratory testing):
   ```
   Test Case: Login with Invalid Credentials
   Date: 2026-02-14
   Tester: QA Engineer
   Result: PASS / FAIL
   Notes: ...
   ```

4. **Defect Tracking** — Using GitHub Issues or JIRA:
   - Issue title, description, severity
   - Steps to reproduce
   - Actual vs. expected result
   - Linked test case ID

**Test Result Aggregation:**
- Weekly test execution summary (test count, pass rate, new defects)
- Trend analysis (defect discovery rate, test stability)

---

### 2.6 Test Metrics

**Metrics Tracked:**

1. **Test Coverage**
   - Code Coverage: % of functions/methods exercised by tests (Target: 70%+)
   - Feature Coverage: % of requirements with corresponding test cases (Target: 100%)
   - Branch Coverage: % of conditional branches tested (Target: 60%+)

2. **Test Execution**
   - Test Count: Total number of test cases (Currently: 50+)
   - Pass Rate: % of tests passing (Target: 95%+)
   - Defect Escape Rate: Defects found after testing / total defects (Target: <5%)

3. **Defect Metrics**
   - Defect Density: # defects / 1000 lines of code (Baseline: TBD)
   - Defect Distribution: By severity (Critical 0, High <5%, Medium <10%, Low <15%)
   - Defect Resolution Time: Average days from report to fix (Target: <3 days)

4. **Performance Metrics**
   - API Response Time: Average < 500ms, P95 < 1000ms
   - Test Execution Duration: Full suite < 20 minutes
   - Database Query Count: GET /api/sales with 100 records ≤ 5 queries

---

### 2.7 Testing Tools and Environment

**Testing Tools:**

| Tool | Purpose | Version |
|------|---------|---------|
| **PHPUnit** | Unit & integration testing framework | 10.0+ |
| **Postman** | API request/response testing | Latest desktop |
| **Newman** | Postman CLI for automation | Latest |
| **Browser DevTools** | UI/E2E verification (Network/Console) | Latest |
| **Git** | Version control, CI/CD trigger | 2.30+ |
| **GitHub Actions** | Continuous Integration/Deployment | Built-in |
| **Laravel Artisan** | CLI for migrations, seeders, serving | Built-in |
| **MySQL** / **SQLite** | Database (test & prod) | 5.7+ / Latest |
| **Docker** (optional) | Environment containerization | 20.10+ |

**Test Environment Setup:**

1. **Local Development:**
   - OS: Windows/Mac/Linux with XAMPP or Laravel Sail
   - Database: SQLite (in-memory for speed) or MySQL
   - Server: `php artisan serve` (localhost:8000)

2. **CI/CD Environment (GitHub Actions):**
   - OS: Ubuntu Linux (latest)
   - Database: MySQL service container
   - Server: Auto-started, runs tests, stops
   - Artifacts: Coverage reports, test logs uploaded

**Environment Configuration:**
- `.env.testing` file specifies test database, app debug mode, etc.
- Database migrations run before tests
- Test seeders populate initial data (users, roles, permissions)
- Database reset (fresh) before each test suite run

---

### 2.8 Test Schedule

**Testing Timeline (Phase 4: API & Testing)**

| Activity | Duration | Start | End | Deliverables |
|----------|----------|-------|-----|--------------|
| Test Plan & Requirements Analysis | 3 days | Feb 10 | Feb 13 | Test Plan, Test Cases (50+), Assumptions Doc |
| Test Automation Implementation | 4 days | Feb 13 | Feb 17 | PHPUnit tests, Postman collection, CI/CD workflow |
| Initial Test Execution & Defect Logging | 2 days | Feb 17 | Feb 19 | Bug reports, test execution log, initial defect list |
| Defect Fix & Regression Testing | 2 days | Feb 19 | Feb 21 | Fixed code, re-execution results |
| Final QA & Documentation | 1 day | Feb 21 | Feb 22 | Final test report, coverage metrics, sign-off |
| **Total** | **~10 days** | **Feb 10** | **Feb 22** | |

**Milestones:**
- **Feb 15 (Wed):** Test plan & test cases ready for review
- **Feb 17 (Fri):** Initial CI/CD pipeline operational
- **Feb 20 (Mon):** 80%+ test pass rate, critical defects resolved
- **Feb 22 (Wed):** Phase 4 complete, ready for Phase 5 (Frontend)

---

## 3.0 TEST PROCEDURE

This section describes detailed test procedures, including test tactics, test cases, and expected results for each testing level.

### 3.1 Software (SCI's) to be Tested

*Same as* **Section 2.1** — All API endpoints, models, database, business logic layers.

---

### 3.2 Testing Procedure

#### 3.2.1 Unit Test Cases & Procedures

**Objective:** Test individual Model and Service methods in isolation.

**Components Under Test:**
1. Product Model (with relationships, scopes)
2. Stock Service (stock increments/decrements)
3. Sale Service (sale creation, validation)
4. Purchase Service (purchase creation, totals)
5. Alert Service (low-stock detection)

**Implementation Location:** `/tests/Unit/StockServiceTest.php`, `/tests/Unit/SaleServiceTest.php`, `/tests/Unit/PurchaseServiceTest.php`

**Sample Test Cases:**

| Test ID | Test Name | Preconditions | Steps | Expected Result |
|---------|-----------|---------------|-------|-----------------|
| UT-004 | Stock incremented after purchase | Product with current_stock=10 | 1. Create Purchase record<br>2. Create PurchaseItem (qty=5)<br>3. Call StockService::updateStockAfterPurchase | Product current_stock=15; StockHistory entry created |
| UT-005 | Stock decremented after sale | Product with current_stock=20 | 1. Create Sale record<br>2. Create SaleItem (qty=5)<br>3. Call StockService::updateStockAfterSale | Product current_stock=15; StockHistory logged |
| UT-006 | Overselling prevented | Product with current_stock=3 | 1. Attempt to create Sale (qty=5)<br>2. Call validation | Exception thrown: "Insufficient stock" |
| UT-007 | Manual stock adjustment logged | Product with current_stock=10 | 1. Create StockAdjustment<br>2. Call StockService::processAdjustment | Stock updated; history with reason logged |
| UT-008 | Low-stock alert generated | Product stock=2, threshold=5 | 1. Call AlertService::checkLowStockProducts | Alert record created with status='unresolved' |

**Pass/Fail Criteria:** All assertions succeed; code coverage ≥80%; execution time <2 seconds.

---

#### 3.2.2 Integration Testing Procedure

**Objective:** Test API endpoints end-to-end with actual HTTP requests.

**Implementation Location:** CI/CD pipeline (`.github/workflows/test-and-newman.yml`)

**Test Cases:**

| IT ID | Test Name | Request | Expected Response |
|-------|-----------|---------|-------------------|
| IT-001 | Admin login success | POST /api/v1/login (admin credentials) | 200 OK; access_token returned |
| IT-002 | Invalid credentials rejected | POST /api/v1/login (wrong password) | 401 Unauthorized |
| IT-005 | Create product success | POST /api/v1/products (valid body) | 201 Created; product returned |
| IT-006 | Duplicate SKU rejected | POST /api/v1/products (existing SKU) | 422 Unprocessable Entity |
| IT-011 | Create sale - sufficient stock | POST /api/v1/sales (stock available) | 201 Created; stock decremented |
| IT-012 | Prevent overselling | POST /api/v1/sales (stock insufficient) | 422 Unprocessable Entity |

**Pass/Fail Criteria:** Correct HTTP status, response body structure, database side effects verified.

---

#### 3.2.3 Validation Testing Procedure

**Purpose:** Ensure input validation works correctly for all endpoints.

| VT ID | Test Name | Field | Invalid Value | Expected Result |
|-------|-----------|-------|---------------|-----------------|
| VT-001 | Product SKU required | sku | (omit) | 422; "sku is required" |
| VT-002 | Unique barcode enforced | barcode | "EXISTING-BARCODE" | 422; "barcode already taken" |
| VT-003 | Sale quantity must be positive | items[0].quantity | -5 | 422; "must be positive" |
| VT-004 | Sale price must be positive | items[0].unit_price | 0 | 422; "must be > 0" |

**Expected Results:** Clear validation error messages; no invalid data persists to database.

---

#### 3.2.4 High-Order Testing (System & Non-Functional)

**Security Testing (SQL Injection Prevention):**

| SEC ID | Test Name | Payload | Expected Behavior |
|--------|-----------|---------|-------------------|
| NF-005 | SQL injection in filter | GET /api/v1/products?category_id=1 OR 1=1 | Safely parameterized; injection payload ignored |
| NF-005B | SQL injection with quotes | GET /api/v1/products?name=" OR DROP TABLE ...;" | Treated as literal; no SQL error |

**Performance Testing:**

| PERF ID | Test Name | Endpoint | Load | Expected Result |
|---------|-----------|----------|------|-----------------|
| NF-001 | Response time < 500ms | GET /api/v1/products | 10 concurrent | Mean < 500ms |
| NF-004 | N+1 prevention | GET /api/v1/sales | 100 sales | ≤ 5 SQL queries |

**Transaction Atomicity:**

| ATOM ID | Test Name | Scenario | Expected Result |
|---------|-----------|----------|-----------------|
| NF-002 | Purchase rollback on error | Error mid-transaction | All changes rolled back; stock unchanged |

---

### 3.3 Testing Resources and Staffing

*Same as* **Section 2.3** — QA Engineer, Developer, DevOps responsibilities and skills.

---

### 3.4 Test Work Products

*Same as* **Section 2.4** — Test plan, test cases, test scripts, Postman collection, defect reports.

---

### 3.5 Test Record Keeping and Test Log

**Test Log Format:**

```
Test Execution Log — February 14, 2026

Test Suite: Unit Tests (StockServiceTest)
Tester: QA Engineer
Date/Time: 2026-02-14 10:30 AM
Environment: Local XAMPP, SQLite in-memory
Duration: 2 seconds

Results:
  Total Tests: 8
  Passed: 8
  Failed: 0
  Coverage: StockService 85%

Notes: All tests passed successfully.
```

**Defect Log Format:**

```
Defect ID: DEF-001
Title: Overselling not prevented
Severity: Critical
Description: Sale created with qty > available stock
Steps to Reproduce:
  1. Create product with stock=3
  2. POST /api/v1/sales with qty=10
Expected: 422 Unprocessable Entity
Actual: 201 Created; stock becomes -7
Date Logged: 2026-02-14
Status: Open
```

---

## APPENDIX A — Test Data & Assumptions

See `/docs/assumptions.md` for:
- 13 database tables and column specifications
- Eloquent relationship mappings
- API endpoint routes and request/response formats
- Test user credentials and roles
- Business logic assumptions

---

## APPENDIX B — Test References

**Key Artifacts:**
- [Test Cases CSV](./docs/test-cases.csv) — 55+ detailed test cases with IDs, steps, expected results
- [Traceability Matrix](./docs/traceability.csv) — Requirements-to-test mapping (43 requirements)
- [API Test Collection](./postman/FRACA-SERVCOM-API.postman_collection.json) — 24 Postman requests for manual/automated testing
- [PHPUnit Tests](./tests/Unit/) — StockServiceTest, SaleServiceTest, PurchaseServiceTest (29 test methods)
- [CI/CD Pipeline](./github/workflows/test-and-newman.yml) — Automated GitHub Actions workflow
- [Testing Guide](./docs/run-tests.md) — Instructions for local and CI test execution

---

## SUMMARY

This Software Test Plan provides a structured, comprehensive approach to testing the FRACA SERVCOM Inventory Management System. By following this plan, we will:

1. ✅ Systematically validate all functional requirements
2. ✅ Catch critical bugs in stock management and transactions early
3. ✅ Ensure data integrity and security standards are met
4. ✅ Enable automated regression testing via CI/CD
5. ✅ Document test coverage and quality metrics
6. ✅ Provide confidence for production deployment

---

**Approval & Sign-Off:**

| Role | Name | Date | Signature |
|------|------|------|-----------|
| QA Lead | TBD | TBD | _____ |
| Development Manager | TBD | TBD | _____ |
| Project Manager | TBD | TBD | _____ |

---

*Version 1.0 — February 14, 2026*  
*For questions or updates, contact: Development Team*
