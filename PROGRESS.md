# E-Book Store — Build Progress

## CURRENT STATE
Phase 2 complete (commit 39b2f23). Full schema migrated (is_admin, categories, books, orders, order_items, book_user), models with relationships + `User::ownsBook`, factories, BookStoreSeeder (1 admin, 3 customers, 5 categories, 20 published books with valid placeholder PDFs on private disk), admin Gate in AppServiceProvider. Verified via script: seed counts, private-disk files, ownsBook, Gate all correct. Test suite still green. Next step: Phase 3.1 — admin dashboard page (`/admin`).

## DECISIONS
- Environment: Windows 11 + XAMPP, PHP 8.5.5, Composer 2.9.7, Node 24.14.0, npm 11.9.0.
- Used `laravel/react-starter-kit` v1.0.1 (the official Laravel 12 React+Inertia starter kit, successor to Breeze React). Laravel 12, React 19, Tailwind 4, TypeScript, shadcn/ui components, Pest tests.
- Database: SQLite (spec allows it for local dev; XAMPP ships MariaDB, not PostgreSQL). Schema kept Postgres-compatible.
- Added a `private` disk (serve=false) alongside the default `local` disk; both root at `storage/app/private`. S3 config already present in filesystems.php for production.
- Test suite shows PHP 8.5 deprecation notices (`PDO::MYSQL_ATTR_SSL_CA`) from framework internals — harmless, all assertions pass.
- Cashier v16.5.3, stripe-php v17.6.0, motion + canvas-confetti via npm.

## CHECKLIST

### Phase 1 — Project setup
- [x] 1.1 DONE — `laravel/react-starter-kit` v1.0.1 scaffolded; auth confirmed via passing Pest suite (login/register/logout/password-reset tests)
- [x] 1.2 DONE — `.env` uses SQLite (`database/database.sqlite`); initial migrations ran (users, cache, jobs)
- [x] 1.3 DONE — `private` disk added to `config/filesystems.php`, root `storage/app/private`, serve=false
- [x] 1.4 DONE — `composer require laravel/cashier` (v16.5.3); `npm install motion canvas-confetti`
- [x] 1.5 DONE — git init + root commit 0206d43

### Phase 2 — Database & models
- [x] 2.1 DONE — migration `2026_06_12_225823_add_is_admin_to_users_table` (boolean, default false)
- [x] 2.2 DONE — migrations + models for categories, books, orders, order_items, book_user (unique book_id+user_id, nullable order_id FK)
- [x] 2.3 DONE — relationships in User/Book/Category/Order/OrderItem (plus inverse: Category hasMany Books, Book belongsToMany owners)
- [x] 2.4 DONE — `User::ownsBook()` in app/Models/User.php
- [x] 2.5 DONE — 4 factories + BookStoreSeeder; placeholder PDFs generated per book at `ebooks/{slug}.pdf` on private disk; seeder verified
- [x] 2.6 DONE — `Gate::define('admin', ...)` in AppServiceProvider::boot

### Phase 3 — Admin panel (book CRUD)
- [ ] 3.1 Admin dashboard (`/admin`): counts of books, orders, total revenue
- [ ] 3.2 Book index (`/admin/books`): paginated table with edit/delete
- [ ] 3.3 Book create form (`/admin/books/create`), multipart FormData
- [ ] 3.4 Store endpoint with FormRequest validation; file → private disk, cover → public disk
- [ ] 3.5 Book edit form; optional file re-upload deletes old file
- [ ] 3.6 Delete endpoint: DB row + files removed
- [ ] 3.7 Admin orders page (`/admin/orders`)
- [ ] 3.8 Feature test: non-admin gets 403 on every `/admin` route

### Phase 4 — Public catalog & search
- [ ] 4.1 Catalog page (`/`): published books, paginated
- [ ] 4.2 Search `q` (title/author), category filter, price sort as Eloquent scopes
- [ ] 4.3 Book detail page (`/books/{slug}`) with Buy / "In your library"
- [ ] 4.4 Guests browse freely; buying requires login with intended URL redirect

### Phase 5 — Payments (Stripe) & library
- [ ] 5.1 Configure Cashier with Stripe test keys (placeholders in `.env.example`)
- [ ] 5.2 Buy endpoint (`POST /checkout/{book}`): pending order + DB-priced order_items + Stripe Checkout session
- [ ] 5.3 Webhook `checkout.session.completed`: mark paid, grant entitlement (ONLY place)
- [ ] 5.4 Success page (thank-you only, no access grant); cancel page
- [ ] 5.5 My Library page (`/library`) with Download buttons
- [ ] 5.6 Download endpoint with `ownsBook` check + private disk download
- [ ] 5.7 Customer order history page (`/orders`)
- [ ] 5.8 Feature tests: price tampering, webhook grants access, success page doesn't, non-owner 403 on download

### Phase 6 — Playful animated frontend
- [ ] 6.1 3D book covers (CSS perspective + spine from accent_color, hover spring tilt)
- [ ] 6.2 Animated search results (layout + AnimatePresence)
- [ ] 6.3 Page transitions for Inertia navigation
- [ ] 6.4 Purchase celebration: confetti + book flying into library nav icon
- [ ] 6.5 Friendly empty states
- [ ] 6.6 Hard rules: transform/opacity only; prefers-reduced-motion; calm checkout

### Phase 7 — Polish & verification
- [ ] 7.1 Full test suite green
- [ ] 7.2 Manual verification checklist (record results here)
- [ ] 7.3 Write README.md (setup, env vars, Stripe webhook, php.ini limits, admin credentials)
- [ ] 7.4 Final commit; mark everything complete
