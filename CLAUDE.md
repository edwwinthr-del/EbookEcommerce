# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Status

This is a greenfield project. The full build specification lives in `ebook-store-agent-prompt.md` — read it before doing anything. It defines an e-book store (admin manages/uploads books; customers buy via Stripe and download from "My Library") to be built in 7 strictly ordered phases.

## Mandatory Workflow

- `PROGRESS.md` in the project root is the source of truth for build state. Read it FIRST at the start of every session, then continue from the first unchecked step. Never redo completed steps.
- After completing every numbered step from the spec, update `PROGRESS.md`: mark the step done with a one-line note, refresh the `## CURRENT STATE` section, and log any technical decisions under `## DECISIONS`.
- Work through phases strictly in order. Commit to git after every phase.
- Do not add features not in the spec (no reviews, no coupons, no wishlists). If something is ambiguous, pick the simplest option consistent with the spec and log it under DECISIONS — don't stop to ask.

## Tech Stack (fixed — do not substitute)

- Laravel 11+ (PHP 8.3+), React 18+ with Inertia.js (Breeze React starter kit)
- PostgreSQL (SQLite acceptable for local dev), Laravel Storage with a `private` local disk at `storage/app/private`
- Laravel Cashier (Stripe) with Stripe Checkout + webhooks
- Tailwind CSS, Vite, Framer Motion (`motion` package), canvas-confetti

## Commands

Standard Laravel + Vite project (once scaffolded; runs under XAMPP on Windows):

```
composer install && npm install
php artisan migrate --seed
php artisan serve          # backend at localhost:8000
npm run dev                # Vite dev server
php artisan test           # full test suite
php artisan test --filter=TestName   # single test
stripe listen --forward-to localhost:8000/stripe/webhook   # local webhook testing
```

Seeded admin login: `admin@example.com` / `password`.

## Non-Negotiable Security Rules

These apply to every change, everywhere:

1. E-book files live on the PRIVATE disk and must never be publicly URL-accessible. Covers go on the public disk; e-book binaries never go in the database, and `file_path` values are never exposed in any public API/page response.
2. Checkout prices are always read from the database — never trusted from the client. `order_items.price` is copied from `books.price` at purchase time so later price edits don't affect past orders.
3. Library entitlement (the `book_user` pivot) is granted ONLY inside the verified Stripe webhook handler (`checkout.session.completed`, matched by `stripe_session_id`). The `/checkout/success` page is cosmetic and must never grant access.
4. All `/admin` routes are protected server-side by `middleware(['auth', 'can:admin'])` using the `admin` Gate (`is_admin` flag on users). Hiding admin UI in React is cosmetic only.
5. Downloads go through `GET /library/{book}/download` which checks `$user->ownsBook($book)` (403 otherwise) and streams via `Storage::disk('private')->download(...)`.

## Architecture

- **Roles**: single `users` table with an `is_admin` boolean; admin Gate defined as `Gate::define('admin', fn ($user) => $user->is_admin)`.
- **Core tables**: `categories`, `books` (slug, price, `cover_path`, `file_path`, `file_format` pdf|epub, `accent_color`, status draft|published), `orders` (status pending|paid|failed, `stripe_session_id`), `order_items` (price snapshot), and the `book_user` pivot as the entitlement/library table (unique on book_id+user_id, references the granting order).
- **Purchase flow**: `POST /checkout/{book}` → create pending order + order_items from DB price → Stripe Checkout session → webhook marks order paid and attaches books to `book_user` → book appears in `/library`.
- **Frontend**: Inertia pages (no separate API). Public catalog at `/` shows published books only, with search (`q` on title/author), category filter, and price sort implemented as Eloquent scopes.
- **Animation rules** (Phase 6): animate only `transform` and `opacity`; respect `prefers-reduced-motion`; checkout flow stays calm — confetti/celebration only on the post-payment success page.

## Environment Notes

- File uploads up to 50 MB: `upload_max_filesize` and `post_max_size` must be raised in php.ini (XAMPP: `C:\xampp\php\php.ini`).
- Stripe test keys go in `.env`; placeholders belong in `.env.example`.
