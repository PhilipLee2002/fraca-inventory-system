# FRACA SERVCOM — development reference

Authoritative feature list for the **shop inventory system** (not the public website).

- **Client:** Fraca Servcom Ltd — furniture and bags (Eldoret). No hardware line.
- **Stack:** Laravel 12, PHP 8.2, SQLite or MySQL, Blade + Bootstrap 5 + Axios
- **Website:** catalog + contact only. One-way import via `database/scripts/extract-website-catalog.mjs`

---

## Launch (in the app now)

* Login only (no public register). Four named users: Benjamin (Admin), Anne (Manager), Franklin (Manager), Receptionist (Staff)
* Permission check on every API verb. Staff cannot change prices/stock or see `cost_price`
* Products/categories from the website snapshot; stock and cost start at 0
* Customers (including walk-in). Sales with cash, M-Pesa (reference required), bank, card
* Stock decreases only when sale status is **completed**
* Purchases and stock adjustments (store / manager)
* Dashboard: today and this week by payment method
* A4 invoice PDF from a sale (`/sales/{id}/invoice`)
* CSV reports for accountant; P&amp;L and valuation hidden from reception

---

## Later (do not build until they confirm)

* Quotes module, deposits / balance, credit ledger
* VAT 16% and KRA eTIMS tax invoices
* Workshop materials / BOM, made-to-order jobs
* Multi-location stock, barcode scanner
* Live “in stock” or prices pushed back to fracaservcom.co.ke
* Website cart / M-Pesa checkout
* Email low-stock jobs

---

## Core inventory (implemented)

* Product CRUD with SKU uniqueness, search, category, website photo URL
* Categories grouped (home, office, storage, seating, bags, church)
* Manual stock adjustments with history
* Automatic stock on **received** purchases and **completed** sales
* Low-stock dashboard alerts

## Sales & purchases (implemented)

* Purchase orders with line items
* Sales invoices with line items; walk-in allowed
* M-Pesa / Till reference on transfer payments
* Printable A4 invoice (not eTIMS)

## Users & security (implemented)

* Breeze session auth; API under `web` + `auth` + `permission:*`
* Roles: Admin, Manager, Staff
* User management (admin). Receptionist is first-line for password questions
* CSRF on the Blade app; inactive users cannot log in

## Reporting (implemented)

* Sales by period + payment method
* Purchases, stock levels, inventory valuation, P&amp;L, stock movement
* CSV export. PDF invoice. No Chart.js requirement for launch
