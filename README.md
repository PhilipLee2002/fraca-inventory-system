# FRACA SERVCOM Inventory Management System

Shop inventory for **Fraca Servcom Ltd** (Musco Towers, Eldoret): **furniture and bags**. Hardware is not sold and is not imported.

The public website ([fracaservcom.co.ke](https://fracaservcom.co.ke), local copy in `FRACA-SERVCOM-WEBSITE/`) is a catalog and contact form only. It does **not** create sales. Reception records a sale in this app after a client is serious.

```
Public website  →  photos / names / prices snapshot  →  inventory catalog
WhatsApp / form  →  Reception  →  type the sale here
```

## Quick start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev
```

Then open **http://127.0.0.1:8000/login** (or run `npm run open` / double-click `open-inventory.cmd`). Ignore Vite’s `localhost:5173` — that is only CSS/JS, not the app.

Default database in `.env.example` is **SQLite**. Switch to MySQL in `.env` if you prefer.

Refresh the catalog after website gallery changes:

```bash
node database/scripts/extract-website-catalog.mjs
php artisan db:seed --class=CategoriesTableSeeder
php artisan db:seed --class=ProductsTableSeeder
```

Opening stock stays **0** until Franklin/Anne count it. Cost stays **0** until they enter it.

## Who logs in

Public registration is **disabled**. Seeded accounts (password from `FRACASERVCOM_STAFF_PASSWORD`, default `Frac@Servcom2026` — change after first login):

| Person | Email | Role |
|--------|--------|------|
| Benjamin Shitsukane (MD) | benjamin@fracaservcomltd.co.ke | Admin |
| Anne Jerubet (Accountant) | anne@fracaservcomltd.co.ke | Manager |
| Franklin Shitsukane (Store) | franklin@fracaservcomltd.co.ke | Manager |
| Receptionist | reception@fracaservcomltd.co.ke | Staff |

Old demo emails (`admin@inventory.com`, …) are deactivated on seed.

Reception can create customers and sales, not prices, cost, or reports. Anne sees daily/weekly totals by cash / M-Pesa / bank. Staff never see `cost_price`.

## What launch includes

- Products and categories from the live furniture + bags catalog
- Walk-in or named customer (churches/companies)
- Cash, M-Pesa (Till reference required), bank, card
- Stock drops only when a sale is **completed**
- Printable A4 invoice PDF (not KRA eTIMS)
- Dashboard: today and this week by payment method

**Not in this phase:** quotes, deposits, VAT/eTIMS, workshop materials, barcode scanner, live website stock, cart checkout.

## Docs

| File | Purpose |
|------|---------|
| [docs/user-manual.md](docs/user-manual.md) | How staff use the shop system |
| [docs/system-architecture.md](docs/system-architecture.md) | How the app is built |
| [docs/implementation-strategy.md](docs/implementation-strategy.md) | Stack, RBAC, deploy |
| [FRACA-SERVCOM-WEBSITE/README.md](FRACA-SERVCOM-WEBSITE/README.md) | Public site (catalog + contact) |
| [DEVELOPMENT_PROGRESS.md](DEVELOPMENT_PROGRESS.md) | What shipped, later work, **before-production checklist** |

## Tech

Laravel 12, PHP 8.2, session auth (Breeze), Blade + Bootstrap 5 + Axios, Vite, SQLite or MySQL. Timezone: `Africa/Nairobi`.

Developer: Philip Lee. Client: Fraca Servcom Ltd.
