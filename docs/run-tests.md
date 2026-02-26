# Running Tests — FRACA SERVCOM Inventory Management System

**Document Date:** February 14, 2026  
**Target Audience:** Developers, QA Engineers, CI/CD Pipelines

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Local Testing Setup](#local-testing-setup)
3. [Running PHPUnit Tests](#running-phpunit-tests)
4. [Running Postman API Tests](#running-postman-api-tests)
5. [CI/CD Pipeline (GitHub Actions)](#cicd-pipeline-github-actions)
6. [Test Data Seeding](#test-data-seeding)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

For a quick sanity check of all tests:

```bash
# 1. Ensure dependencies are installed
composer install

# 2. Run all tests (unit + integration)
composer test

# 3. Seed test data (if needed)
php artisan db:seed --class=PermissionSeeder
```

Expected output: ✅ All tests pass

---

## Local Testing Setup

### Prerequisites

- **PHP 8.2+**
- **MySQL 5.7+** OR **SQLite** (for in-memory testing)
- **Composer** (PHP dependency manager)
- **Node.js 16+** (for Newman/Postman CLI testing)
- **Git** (for CI/CD integration)

### Environment Setup

#### 1. Create Test Environment File

```bash
cp .env.example .env.testing
```

#### 2. Configure `.env.testing`

```dotenv
APP_ENV=testing
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
# OR for MySQL test database:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=fraca_inventory_test
# DB_USERNAME=root
# DB_PASSWORD=
```

#### 3. Install Dependencies

```bash
composer install
npm install -g newman newman-reporter-htmlextra
```

#### 4. Generate App Key

```bash
php artisan key:generate --env=testing
```

#### 5. Create Database

For SQLite (in-memory, fastest):
```bash
touch database/testing.sqlite
```

For MySQL:
```bash
mysql -u root -p -e "CREATE DATABASE fraca_inventory_test;"
```

#### 6. Run Migrations

```bash
php artisan migrate --env=testing --database=sqlite
# OR
php artisan migrate --env=testing --database=mysql
```

---

## Running PHPUnit Tests

### What is PHPUnit?

PHPUnit is a unit testing framework for PHP. It tests:
- **Unit tests:** Individual functions/methods in isolation
- **Integration tests:** Multiple components working together
- **Feature tests:** Full API endpoint workflows

### Run All Tests

```bash
composer test
```

Equivalent to:
```bash
vendor/bin/phpunit
```

### Run Specific Test File

```bash
# Test stock service
vendor/bin/phpunit tests/Unit/StockServiceTest.php

# Test sale service
vendor/bin/phpunit tests/Unit/SaleServiceTest.php

# Test purchase service
vendor/bin/phpunit tests/Unit/PurchaseServiceTest.php
```

### Run Specific Test Method

```bash
vendor/bin/phpunit tests/Unit/StockServiceTest.php --filter test_stock_incremented_after_purchase
```

### Run with Coverage Report

```bash
vendor/bin/phpunit --coverage-html=coverage/
# View: open coverage/index.html in browser
```

### Run Tests in Parallel (faster)

```bash
vendor/bin/phpunit --processes=4
```

### Run Unit Tests Only

```bash
vendor/bin/phpunit tests/Unit
```

### Run Integration/Feature Tests Only

```bash
vendor/bin/phpunit tests/Feature
```

### Sample PHPUnit Output

```
PHPUnit 10.0.0 by Sebastian Bergmann and contributors.

.FF.S..                 7 tests, 1 passed, 2 failed, 1 skipped, 3 warnings

Time: 00:04.567, Memory: 12.34 MB

FAILURES:
--
1) Tests\Unit\StockServiceTest::test_overselling_prevented
Failed asserting that exception of type "InsufficientStockException" is thrown.
...
```

**Legend:**
- `.` = Pass
- `F` = Fail
- `S` = Skip
- `I` = Incomplete

---

## Running Postman API Tests

### What is Newman?

Newman is the CLI tool for running Postman collections. It allows automated testing of API endpoints.

### Prerequisites

```bash
npm install -g newman newman-reporter-htmlextra
```

### 1. Start Laravel Server

```bash
php artisan serve
# Starts on http://localhost:8000
```

### 2. Run Postman Collection

```bash
newman run postman/FRACA-SERVCOM-API.postman_collection.json \
  -e <(echo '{"values":[{"key":"base_url","value":"http://localhost:8000/api/v1"}]}') \
  --reporters cli,htmlextra
```

### 3. Generate HTML Report

```bash
newman run postman/FRACA-SERVCOM-API.postman_collection.json \
  -e <(echo '{"values":[{"key":"base_url","value":"http://localhost:8000/api/v1"}]}') \
  --reporters htmlextra \
  --reporter-htmlextra-export "postman-results.html"

# Open report
open postman-results.html
```

### 4. Use Custom Environment File

Create `postman-env.json`:
```json
{
  "id": "test-env",
  "name": "Test Environment",
  "values": [
    {"key": "base_url", "value": "http://localhost:8000/api/v1"},
    {"key": "admin_token", "value": ""},
    {"key": "staff_token", "value": ""}
  ]
}
```

Then run:
```bash
newman run postman/FRACA-SERVCOM-API.postman_collection.json \
  -e postman-env.json \
  --reporters cli
```

### What Tests Are Included?

The Postman collection covers:

| Endpoint | Method | Test Case |
|----------|--------|-----------|
| `/login` | POST | Admin & Staff login |
| `/logout` | POST | Session termination |
| `/products` | GET | List with pagination |
| `/products/{id}` | GET | Get product details |
| `/products` | POST | Create product |
| `/products/{id}` | PUT | Update product |
| `/products/{id}` | DELETE | Delete product |
| `/sales` | GET | List sales |
| `/sales` | POST | Create sale/invoice |
| `/sales/{id}` | GET | Get sale details |
| `/purchases` | GET | List purchases |
| `/purchases` | POST | Create purchase order |
| `/purchases/{id}` | GET | Get purchase details |
| `/stock-adjustments` | POST | Manual stock adjustment |
| `/reports/sales` | GET | Sales report |
| `/reports/purchases` | GET | Purchase report |
| `/reports/stock-levels` | GET | Stock levels report |
| `/reports/inventory-valuation` | GET | Inventory valuation |

---

## CI/CD Pipeline (GitHub Actions)

### What Happens on Git Push/PR?

Automated workflow runs:
1. **PHP Lint** — Syntax validation
2. **Composer Dependency Check** — Version compatibility
3. **Database Migrations** — Schema setup
4. **PHPUnit Tests** — All unit & integration tests
5. **Newman Tests** — All API endpoint tests
6. **Code Coverage Report** — Upload to Codecov

### View GitHub Actions Status

1. Go to: `https://github.com/PhilipLee2002/fraca-inventory-system/actions`
2. Click on the latest workflow run
3. View logs for each job (PHPUnit, Postman, etc.)

### Workflow Configuration

File: `.github/workflows/test-and-newman.yml`

**Jobs:**
- `phpunit-tests` — Runs all PHPUnit tests
- `postman-tests` — Runs Newman API tests
- `test-summary` — Reports overall status

**Triggers:**
- On push to `main` or `develop` branches
- On pull request against `main` or `develop`

### Re-run Failed Tests

```bash
# If a test fails in CI, you can reproduce locally by running:
git checkout <branch>
php artisan migrate --database=sqlite
vendor/bin/phpunit tests/Unit/StockServiceTest.php
```

---

## Test Data Seeding

### Seed Initial Data

```bash
# Seed all (roles, permissions, users, products, etc.)
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=RolesTableSeeder
php artisan db:seed --class=UsersTableSeeder
php artisan db:seed --class=PermissionSeeder
```

### What Gets Seeded?

| Seeder | Records Created |
|--------|-----------------|
| `RolesTableSeeder` | Admin, Staff roles |
| `UsersTableSeeder` | admin@test.local, staff@test.local |
| `PermissionSeeder` | 15+ permissions, role assignments |
| `CategoriesTableSeeder` | 5 product categories |
| `SuppliersTableSeeder` | 3 suppliers |
| `CustomersTableSeeder` | 3 customers |
| `ProductsSeeder` | 10 sample products |

### Test User Credentials

After seeding, login with:
```
Email: admin@test.local
Password: password

Email: staff@test.local
Password: password
```

### Reset Database

```bash
# Drop all tables and restart
php artisan migrate:fresh

# Drop, migrate, and seed
php artisan migrate:fresh --seed

# For testing environment
php artisan migrate:fresh --database=sqlite --seed
```

---

## Test Data Policy

### Test Database Isolation

- **Unit tests** use SQLite in-memory database (`:memory:`)
  - Faster execution (~1 second for 100 tests)
  - Complete rollback after each test
  - No persistent state

- **Integration/Feature tests** use SQLite file or MySQL
  - Each test wrapped in transaction
  - Rolled back automatically after completion
  - No test data pollution

### Data Cleanup

All tests use Laravel's `RefreshDatabase` trait:

```php
use Illuminates\Foundation\Testing\RefreshDatabase;

class SaleServiceTest extends TestCase {
    use RefreshDatabase; // Auto-rollback after each test
}
```

### Manual Cleanup

```bash
# Clear all test data
php artisan migrate:refresh --database=sqlite

# Remove test database file
rm database/testing.sqlite
```

---

## Troubleshooting

### Common Issues & Solutions

#### Issue: "SQLSTATE[HY000]: General error: 1 table ... already exists"

**Cause:** Migrations not properly rolled back.

**Solution:**
```bash
php artisan migrate:reset --database=sqlite
php artisan migrate --database=sqlite
```

#### Issue: "Class not found: App\Models\Product"

**Cause:** Autoloader cache is stale.

**Solution:**
```bash
composer dump-autoload
php artisan optimize:clear
```

#### Issue: Tests timeout or hang

**Cause:** Laravel server not running or port 8000 in use.

**Solution:**
```bash
# Kill process on port  8000
lsof -ti:8000 | xargs kill -9

# Start fresh
php artisan serve
```

#### Issue: "No application encryption key has been generated"

**Cause:** App key not set in `.env.testing`.

**Solution:**
```bash
php artisan key:generate --env=testing
```

#### Issue: Newman says "Failed to parse the Postman collection"

**Cause:** Invalid JSON in collection file.

**Solution:**
```bash
# Validate JSON
jq . postman/FRACA-SERVCOM-API.postman_collection.json
```

#### Issue: Postman tests pass locally but fail in CI

**Cause:** Environment variables not set correctly in GitHub Actions.

**Solution:**  
Edit `.github/workflows/test-and-newman.yml` and add environment variables:

```yaml
env:
  APP_ENV: testing
  BASE_URL: http://localhost:8000/api/v1
```

---

## Test Coverage Goals

| Category | Target | Current |
|----------|--------|---------|
| Unit Test Coverage | 80%+ | TBD |
| Integration Test Coverage | 70%+ | TBD |
| Critical Path Tests | 100% | TBD |
| API Endpoint Coverage | 100% | TBD |

### Generate Coverage Report

```bash
vendor/bin/phpunit --coverage-html=coverage/ --coverage-clover=coverage.xml

# View HTML report
open coverage/index.html
```

---

## Performance Benchmarks

Expected test execution times:

| Test Suite | Count | Duration |
|-----------|-------|----------|
| Unit Tests (Stock Service) | 8 | ~1 sec |
| Unit Tests (Sale Service) | 7 | ~1.5 sec |
| Unit Tests (Purchase Service) | 8 | ~1.5 sec |
| Integration Tests | 17 | ~5 sec |
| Postman Tests (Newman) | 15 | ~10 sec |
| **Total** | **~55** | **~19 sec** |

---

## Documentation References

- [PHPUnit Documentation](https://phpunit.de/)
- [Newman CLI Guide](https://learning.postman.com/docs/running-collections/using-newman-cli/)
- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [GitHub Actions Docs](https://docs.github.com/en/actions)

---

## Support & Feedback

For issues, questions, or contributions:
- GitHub Issues: https://github.com/PhilipLee2002/fraca-inventory-system/issues
- Contact: Development Team

---

**Last Updated:** February 14, 2026  
**Status:** Ready for use
