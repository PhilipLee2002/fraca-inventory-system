# Local setup

For running the inventory app on your own machine. Do not put staff passwords, phones, or production URLs in git.

## Requirements

PHP 8.2, Composer, Node.js, and SQLite (default) or MySQL.

## Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

In `.env`, set `FRACASERVCOM_STAFF_PASSWORD` to a password **you** choose, then:

```bash
php artisan migrate --seed
php artisan serve
npm run dev
```

Open **http://127.0.0.1:8000/login**. Ignore Vite’s `localhost:5173` — that is only CSS/JS.

On Windows you can also run `npm run open` or double-click `open-inventory.cmd`.

Refresh the catalog after website gallery changes:

```bash
node database/scripts/extract-website-catalog.mjs
php artisan db:seed --class=CategoriesTableSeeder
php artisan db:seed --class=ProductsTableSeeder
```

Opening stock stays 0 until it is counted in the shop. Cost stays 0 until someone with permission enters it.

Public registration is disabled. Seeded people are Admin, Manager (accountant / store), and Staff (reception). Demo `@inventory.com` accounts stay inactive.
