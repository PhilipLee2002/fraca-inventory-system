# FRACA SERVCOM Inventory — development progress

**Client:** Fraca Servcom Ltd (furniture & bags; Eldoret)  
**Repo:** https://github.com/PhilipLee2002/fraca-inventory-system  
**Stack:** Laravel 12, PHP 8.2, Blade, Bootstrap 5, Axios, Vite  
**Last updated:** 17 September 2026

---

## Current product (launch)

The shop system is a single-location POS + stock book for furniture and bags. The public website stays a catalog + contact form. There is **no** live website–stock link.

| Area | Status |
|------|--------|
| Auth, RBAC, no public register | Done |
| Named users (Benjamin, Anne, Franklin, Receptionist) | Done |
| Website catalog import (prices, photos, stock 0) | Done |
| Sales: walk-in, cash/M-Pesa/bank, Till reference | Done |
| Stock drop on **completed** sale only | Done |
| Hide cost from Staff (UI + API) | Done |
| Daily / weekly totals by payment method | Done |
| Printable A4 invoice PDF | Done |
| Quotes, deposits, VAT/eTIMS, workshop BOM | Later |
| Barcode scanner, live site stock, checkout | Later |

Re-seed after pulling: `php artisan migrate --seed`. Catalog refresh: `node database/scripts/extract-website-catalog.mjs` then category/product seeders.

### Seeded logins

Password: `FRACASERVCOM_STAFF_PASSWORD` (default `Frac@Servcom2026`). Change after first login.

- Benjamin (Admin), Anne (Manager / accountant), Franklin (Manager / store), Receptionist (Staff)
- Demo `*@inventory.com` accounts are deactivated

---

## Earlier build (Jan–Mar 2026)

Phases 0–5 delivered the Laravel app: models, Breeze login, permission middleware, JSON APIs, Blade modules (dashboard, products, sales, purchases, reports), and the PHPUnit suite. Phase 6 email jobs and barcode were never started; they stay out of launch.

Do not treat March 2026 “next tasks” (email alerts, Chart.js, barcode) as current priorities. Month-one success is **Anne’s daily sales total**.

---

## How the website and IMS fit

1. Marketing site: photos, names, KSh prices, inquire form, WhatsApp.
2. Extract script snapshots the gallery into `database/data/website-catalog.json`.
3. IMS seeders load that catalog. Franklin/Anne enter opening stock after a count.
4. Reception answers the form/WhatsApp; when the client is serious, they create a customer/sale in IMS.

Hardware is not a product line. `FRACA-SERVCOM-WEBSITE/HARDWARE.html` is empty.

---

## Tests that lock launch behaviour

`php artisan test tests/Feature/FracaLaunchSliceTest.php tests/Feature/ApiEndpointVerificationTest.php tests/Feature/WebsiteCatalogSeederTest.php tests/Feature/Auth/RegistrationTest.php`

---

## Before production (do not skip)

Local login on this PC is **not** production. Do not host, share a public URL, or put a staff login on fracaservcom.co.ke until this list is done. Agent reminder: `.cursor/rules/before-production.mdc`.

### Shop floor

- [ ] Each person sets a **unique** password (retire the shared seed password)
- [ ] Franklin/Anne count furniture and bags, then enter **opening stock** (seeded at 0)
- [ ] Enter **cost** where they want margin later (Staff must still never see `cost_price`)
- [ ] Walk Reception through one sale + A4 invoice; Anne through daily totals; Franklin through a stock adjustment

### Hosting and data

- [ ] Shop server or VPN — not this laptop as the live book
- [ ] MySQL (or similar). Do **not** use SQLite on OneDrive for live sales
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
- [ ] HTTPS and `SESSION_SECURE_COOKIE=true`
- [ ] Daily database backup; restore tested once
- [ ] Forgot-password mail only after a real mailbox exists; until then Benjamin resets passwords in Users

### Security gates (code + config)

- [ ] Rate-limit `POST /api/login` the same way as web login (5 tries / email+IP). JSON login currently issues Sanctum tokens with **no** throttle
- [ ] Do not return raw exception messages from `/api/login` or `/api/verify-admin`
- [ ] Rate-limit or remove `POST /api/verify-admin` (managers can retry Benjamin’s password)
- [ ] Keep demo `@inventory.com` accounts **inactive**
- [ ] Do not advertise IMS on the public website. Contact form must never create customers or sales
- [ ] Treat seeder phone numbers as personal data if the GitHub repo is public
- [ ] `npm audit` on Vite deps before a public deploy
- [ ] Fix PSR-4 so `app/console` commands (e.g. stock alerts) actually autoload, or move them to `app/Console`

### Do not run on a live shop database

`php artisan migrate:fresh` wipes sales. Catalog refresh is extract script + category/product seeders only.

---

## Next (only after a call with Benjamin / Anne / Franklin)

Quotes in-system, deposits/balance, credit, VAT 16% / eTIMS, made-to-order vs ready stock, timber/foam materials, multi-location, barcode, live “in stock” on the website.

Month-one success stays **Anne’s trusted daily sales total**. Do not start the list above until that is true in the shop.
