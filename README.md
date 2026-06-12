# E-Book Store

An e-book store built with Laravel 12, React 19 + Inertia.js, Tailwind CSS 4, and Stripe (Laravel Cashier). Admins manage and upload books (PDF/EPUB); customers browse, search, buy via Stripe Checkout, and download their books from My Library.

## Requirements

- PHP 8.3+ with the `pdo_sqlite` extension (PostgreSQL recommended for production)
- Composer 2
- Node.js 20+ and npm
- A [Stripe](https://stripe.com) account (test mode) and the [Stripe CLI](https://stripe.com/docs/stripe-cli) for local webhooks

## Setup

```bash
composer install
npm install

cp .env.example .env          # on Windows: copy .env.example .env
php artisan key:generate

# SQLite (default for local dev) — create the database file:
# on Windows PowerShell: New-Item -ItemType File database/database.sqlite
touch database/database.sqlite

php artisan migrate --seed    # seeds demo data, see "Seeded accounts" below
php artisan storage:link      # exposes public cover images

npm run build                 # or `npm run dev` while developing
php artisan serve             # http://localhost:8000
```

For PostgreSQL, set `DB_CONNECTION=pgsql` plus the usual `DB_*` variables in `.env` before migrating.

## Environment variables

| Variable | Purpose |
| --- | --- |
| `STRIPE_KEY` | Stripe publishable key (`pk_test_...`) |
| `STRIPE_SECRET` | Stripe secret key (`sk_test_...`) |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret (`whsec_...`), printed by `stripe listen` |
| `CASHIER_CURRENCY` | Checkout currency, defaults to `usd` |

## Stripe webhooks (local)

Library access is granted **only** by the verified `checkout.session.completed` webhook — never by the success page. Locally, forward webhooks with the Stripe CLI:

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Copy the `whsec_...` secret it prints into `STRIPE_WEBHOOK_SECRET` in `.env`.

Test a purchase with Stripe's test card: `4242 4242 4242 4242`, any future expiry, any CVC.

## PHP upload limits

Admin book uploads accept e-book files up to 50 MB. PHP's defaults are far lower, so raise these in `php.ini` (XAMPP: `C:\xampp\php\php.ini`) and restart the web server:

```ini
upload_max_filesize = 51M
post_max_size = 52M
```

## Seeded accounts

`php artisan migrate --seed` creates:

- **Admin:** `admin@example.com` / `password`
- 3 random customers (password: `password`), 5 categories, and 20 published books with placeholder PDFs

The admin panel lives at `/admin` (only visible and accessible to admin users).

## Security model

- E-book files live on a **private disk** (`storage/app/private`) and are never URL-accessible; downloads stream through `/library/{book}/download` after an ownership check.
- Checkout prices always come from the database; client input is never trusted. `order_items.price` snapshots the price at purchase time, so later edits don't affect past orders.
- All `/admin` routes are protected server-side by `auth` + an `admin` gate.

## Tests

```bash
php artisan test                       # full suite
php artisan test --filter=CheckoutTest # single class
```
