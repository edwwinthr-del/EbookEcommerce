# E-Book Store — Build Progress

## CURRENT STATE
PROGRESS.md created. Nothing built yet. Next step: Phase 1.1 — create Laravel project with the React + Inertia starter kit.

## DECISIONS
- Environment: Windows 11 + XAMPP, PHP 8.5.5, Composer 2.9.7, Node 24.14.0, npm 11.9.0.

## CHECKLIST

### Phase 1 — Project setup
- [ ] 1.1 Create Laravel project with React + Inertia starter kit; confirm auth pages work
- [ ] 1.2 Configure `.env` for local database; run initial migrations
- [ ] 1.3 Add `private` local disk to `config/filesystems.php` rooted at `storage/app/private`
- [ ] 1.4 Install `laravel/cashier`, `motion`, `canvas-confetti`
- [ ] 1.5 Initialize git, first commit

### Phase 2 — Database & models
- [ ] 2.1 Add `is_admin` boolean to users via migration
- [ ] 2.2 Migrations + models: categories, books, orders, order_items, book_user pivot
- [ ] 2.3 Relationships: User hasMany Orders; User belongsToMany Books; Book belongsTo Category; Order hasMany OrderItems
- [ ] 2.4 `User::ownsBook(Book $book): bool` helper
- [ ] 2.5 Factories + seeder: 1 admin, 3 customers, 5 categories, 20 published books with placeholder PDFs on private disk
- [ ] 2.6 Admin Gate: `Gate::define('admin', fn ($user) => $user->is_admin);`

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
