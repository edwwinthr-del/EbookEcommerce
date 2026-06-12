
# E-Book Store — Build Progress

## CURRENT STATE
Phase 5 complete (commit b09b4bd). Billable on User, Cashier migrations published+run, Stripe env placeholders in .env/.env.example. POST /checkout/{book} (auth, 403 if owned, 404 if draft) creates pending order + DB-priced order_items, Stripe session via StripeCheckoutService (mockable), session id stored, Inertia::location to Stripe. GET /checkout/{book} resume route makes guest→login→intended flow work. HandleStripeWebhook listener (WebhookReceived, checkout.session.completed) is the ONLY entitlement grant — idempotent, syncWithoutDetaching with order_id. Success page display-only; cancel_url returns to book page. /library with downloads (ownsBook + private disk), /orders history. CheckoutTest: 8 tests incl. all 4 spec-required. Suite: 43 tests / 164 assertions green. Next step: Phase 6.1 — 3D book covers.

## DECISIONS
- Environment: Windows 11 + XAMPP, PHP 8.5.5, Composer 2.9.7, Node 24.14.0, npm 11.9.0.
- Used `laravel/react-starter-kit` v1.0.1 (the official Laravel 12 React+Inertia starter kit, successor to Breeze React). Laravel 12, React 19, Tailwind 4, TypeScript, shadcn/ui components, Pest tests.
- Database: SQLite (spec allows it for local dev; XAMPP ships MariaDB, not PostgreSQL). Schema kept Postgres-compatible.
- Added a `private` disk (serve=false) alongside the default `local` disk; both root at `storage/app/private`. S3 config already present in filesystems.php for production.
- Test suite shows PHP 8.5 deprecation notices (`PDO::MYSQL_ATTR_SSL_CA`) from framework internals — harmless, all assertions pass.
- Cashier v16.5.3, stripe-php v17.6.0, motion + canvas-confetti via npm.
- Tests are class-based PHPUnit 11 (starter kit ships phpunit, NOT Pest, despite a leftover tests/Pest.php). New tests must extend Tests\TestCase with RefreshDatabase.
- Starter kit has 4 pre-existing tsc strict errors in its own files (auth pages, welcome.tsx); not introduced by us, left untouched. Vite build is the gate.
- Slugs are generated server-side from title with numeric suffix on collision; slug regenerates when title changes on update.

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
- [x] 3.1 DONE — Admin/DashboardController + admin/dashboard.tsx (books, orders, paid revenue counts)
- [x] 3.2 DONE — Admin/BookController@index + admin/books/index.tsx (paginate 15, edit/delete actions)
- [x] 3.3 DONE — create form (shared book-form.tsx), multipart via Inertia forceFormData
- [x] 3.4 DONE — StoreBookRequest (file mimes:pdf,epub max 51200; cover image max 2048; price numeric min 0); file→ebooks/ private, cover→covers/ public; php.ini limits documented in README
- [x] 3.5 DONE — edit form; new file upload deletes old private file first (same for cover); slug regenerated on title change
- [x] 3.6 DONE — destroy deletes DB row + e-book file + cover
- [x] 3.7 DONE — Admin/OrderController + admin/orders/index.tsx (customer, items, total, status)
- [x] 3.8 DONE — tests/Feature/Admin/AdminAccessTest.php (403 for all 8 admin routes; guest redirect; admin 200)

### Phase 4 — Public catalog & search
- [x] 4.1 DONE — CatalogController@index + catalog/index.tsx, StoreLayout for public pages, published-only paginate(12)
- [x] 4.2 DONE — scopes on Book (search uses LOWER(...) LIKE for case-insensitivity on SQLite+Postgres); validated query params q/category/sort
- [x] 4.3 DONE — `/books/{slug}` route-model-bound by slug; owned flag from ownsBook; Buy posts to /checkout/{id}
- [x] 4.4 DONE — catalog routes are guest-accessible; checkout will sit behind auth middleware (intended-URL resume handled in Phase 5)

### Phase 5 — Payments (Stripe) & library
- [x] 5.1 DONE — Billable trait, cashier migrations, STRIPE_* + CASHIER_CURRENCY placeholders in .env.example
- [x] 5.2 DONE — CheckoutController@store + StripeCheckoutService (price_data.unit_amount from DB); stripe_session_id stored
- [x] 5.3 DONE — HandleStripeWebhook listener registered in AppServiceProvider; Cashier verifies signature at /stripe/webhook
- [x] 5.4 DONE — checkout/success.tsx ("Yours forever.", display-only); cancel_url = book detail page
- [x] 5.5 DONE — LibraryController@index + library/index.tsx with Download buttons
- [x] 5.6 DONE — download endpoint: abort_unless ownsBook 403, Storage private download as "{title}.{format}"
- [x] 5.7 DONE — OrderHistoryController + orders/index.tsx (paginated 10)
- [x] 5.8 DONE — CheckoutTest: tampering (a), webhook grant (b), success no-grant (c), 403 download (d) + owner download, auth redirect, idempotency extras

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
