# AGENT PROMPT — E-BOOK STORE (Laravel + React)

You are a senior full-stack developer agent. Your job is to build a complete e-book store web application from scratch, working autonomously, step by step. Follow these instructions exactly. Do not skip steps, do not reorder phases, and do not invent features that are not listed here.

---

## 0. PROGRESS TRACKING — MANDATORY, DO THIS FIRST

Before writing any application code, create a file named `PROGRESS.md` in the project root. This file is your memory. Rules:

1. `PROGRESS.md` must contain every phase and step from this prompt as a checklist, in order.
2. After completing EVERY major step (each numbered step below), you MUST immediately update `PROGRESS.md`:
   - Mark the step as `[x] DONE`, with a one-line note of what was created/changed (file names, commands run).
   - Add a `## CURRENT STATE` section at the top describing exactly where you are and what the very next step is.
   - Add a `## DECISIONS` section logging any technical decision you made (package versions, naming, deviations) so it is never lost.
3. At the start of EVERY new working session or context reset: read `PROGRESS.md` FIRST, then continue from the first unchecked step. Never redo completed steps. Never assume — verify against `PROGRESS.md`.
4. If a step fails or is left half-finished, mark it `[~] IN PROGRESS` with a note describing exactly what remains.

Initial structure of `PROGRESS.md`:

```md
# E-Book Store — Build Progress

## CURRENT STATE
(what is done, what is next)

## DECISIONS
(log of technical decisions)

## CHECKLIST
- [ ] Phase 1: Project setup
- [ ] Phase 2: Database & models
... (all phases and steps from this prompt)
```

---

## 1. PROJECT OVERVIEW

Build an e-book store with two roles:

- **Admin**: logs in, manages books (create, edit, delete), uploads PDF/EPUB files, edits prices, views orders.
- **Customer**: registers/logs in, browses and searches the catalog, buys e-books via Stripe, downloads owned books from "My Library".

Non-negotiable security rules (apply everywhere):
- E-book files are stored on a PRIVATE disk. They must NEVER be publicly accessible by URL.
- Checkout prices are ALWAYS read from the database, never trusted from the client.
- Library access (entitlement) is granted ONLY inside the verified Stripe webhook handler — never on the redirect/success page.
- All `/admin` routes are protected by backend middleware (auth + admin gate). Frontend hiding is cosmetic only.

## 2. TECH STACK — USE EXACTLY THIS

- Backend: Laravel 11+ (PHP 8.3+)
- Frontend: React 18+ with Inertia.js (use the official Laravel React starter kit / Breeze with Inertia + React)
- Database: PostgreSQL (SQLite acceptable for local dev only)
- File storage: Laravel Storage. Local: `private` disk at `storage/app/private`. Production-ready config for S3 included but not required to run.
- Payments: Laravel Cashier (Stripe) with Stripe Checkout + webhooks
- Animations: Framer Motion (`motion` package), canvas-confetti
- Styling: Tailwind CSS
- Build tool: Vite

---

## PHASE 1 — PROJECT SETUP

1.1 Create a new Laravel project with the React + Inertia starter kit (Breeze, React stack). Confirm login/register/logout/password-reset pages work.
1.2 Configure `.env` for the local database. Run initial migrations.
1.3 Add a `private` local disk to `config/filesystems.php` rooted at `storage/app/private`.
1.4 Install dependencies: `laravel/cashier`, and on the frontend `motion` (Framer Motion) and `canvas-confetti`.
1.5 Initialize git, make the first commit.
→ UPDATE PROGRESS.md

## PHASE 2 — DATABASE & MODELS

2.1 Add an `is_admin` boolean (default false) to the `users` table via migration.
2.2 Create migrations + Eloquent models with these exact fields:

- `categories`: id, name, slug, timestamps
- `books`: id, title, author, slug (unique), description (text, nullable), price (decimal 8,2), category_id (FK nullable), cover_path (string nullable), file_path (string), file_format (enum: pdf, epub), accent_color (string nullable — hex color the admin picks for the playful UI), status (enum: draft, published; default draft), timestamps
- `orders`: id, user_id (FK), total (decimal 8,2), status (enum: pending, paid, failed; default pending), stripe_session_id (string nullable, indexed), timestamps
- `order_items`: id, order_id (FK), book_id (FK), price (decimal 8,2 — price AT TIME OF PURCHASE, copied from books.price), timestamps
- `book_user` pivot (the library/entitlements table): book_id, user_id, order_id (FK nullable), timestamps; unique constraint on (book_id, user_id)

2.3 Define relationships: User hasMany Orders; User belongsToMany Books (library); Book belongsTo Category; Order hasMany OrderItems.
2.4 Add a `User::ownsBook(Book $book): bool` helper method.
2.5 Create factories and a seeder: 1 admin user (admin@example.com / password, is_admin=true), 3 test customers, 5 categories, 20 fake published books (use placeholder PDF files generated into the private disk so downloads are testable).
2.6 Define an admin Gate: `Gate::define('admin', fn ($user) => $user->is_admin);`
→ UPDATE PROGRESS.md

## PHASE 3 — ADMIN PANEL (BOOK CRUD)

All routes in a group: `Route::middleware(['auth', 'can:admin'])->prefix('admin')`.

3.1 Admin dashboard page (`/admin`): counts of books, orders, total revenue.
3.2 Book index page (`/admin/books`): paginated table with title, price, status, edit/delete actions.
3.3 Book create form (`/admin/books/create`): fields title, author, description, price, category, accent_color (color picker), status, cover image upload, e-book file upload. Submit as multipart FormData.
3.4 Store endpoint with a FormRequest validating:
   - `file`: required, `mimes:pdf,epub`, max 51200 (50 MB)
   - `cover`: nullable, image, max 2048
   - `price`: required, numeric, min 0
   E-book file → `store('ebooks', 'private')`. Cover → `store('covers', 'public')`. Save returned paths to the model. Document in README that `upload_max_filesize` and `post_max_size` must be raised in php.ini.
3.5 Book edit form: all fields editable including price. File re-upload optional — if a new file is uploaded, delete the old one from storage, then save the new path.
3.6 Delete endpoint: delete DB row AND remove its files from storage.
3.7 Admin orders page (`/admin/orders`): paginated list with customer, items, total, status.
3.8 Write a feature test proving a non-admin user gets 403 on every `/admin` route.
→ UPDATE PROGRESS.md

## PHASE 4 — PUBLIC CATALOG & SEARCH

4.1 Catalog page (`/`): published books only, paginated, with cover, title, author, price.
4.2 Search: query param `q` matching title/author (case-insensitive LIKE or Postgres full-text), plus category filter and price sort. Implement as Eloquent scopes.
4.3 Book detail page (`/books/{slug}`): full info + Buy button. If the logged-in user already owns it, show "In your library" linking to downloads instead of Buy.
4.4 Guests can browse and search freely; buying requires login (redirect to login with intended URL).
→ UPDATE PROGRESS.md

## PHASE 5 — PAYMENTS (STRIPE) & LIBRARY

5.1 Configure Cashier with Stripe test keys (placeholders in `.env.example`).
5.2 Buy endpoint (`POST /checkout/{book}`): auth required; abort 403 if user already owns the book; create an `orders` row (status pending) + `order_items` row with price COPIED FROM THE DATABASE; create a Stripe Checkout session with that DB price; store `stripe_session_id` on the order; redirect to Stripe.
5.3 Webhook handler for `checkout.session.completed`: verify signature (Cashier does this); find order by `stripe_session_id`; mark it `paid`; attach the book(s) to the user in `book_user` with the order_id. THIS IS THE ONLY PLACE ENTITLEMENT IS GRANTED.
5.4 Success page (`/checkout/success`): thank-you UI only — it must NOT grant access. Cancel page returns to the book.
5.5 My Library page (`/library`): all books the user owns, each with a Download button.
5.6 Download endpoint (`GET /library/{book}/download`): auth required; `abort_unless($user->ownsBook($book), 403)`; return `Storage::disk('private')->download($book->file_path, $book->title . '.' . $book->file_format)`.
5.7 Order history page (`/orders`) for the customer.
5.8 Feature tests: (a) checkout uses DB price even if request tampers with price, (b) webhook grants library access, (c) success page alone does NOT grant access, (d) non-owner gets 403 on download.
→ UPDATE PROGRESS.md

## PHASE 6 — PLAYFUL ANIMATED FRONTEND

Design language: modern-playful, NOT childish. Serif display font (Fraunces via Google Fonts or fontsource) for headings + clean sans for UI. Slight rotations (-3° to 3°) on cards. Spring-based motion everywhere. Microcopy with personality (e.g. search placeholder: `Try "cozy mystery" or "dragons but make it sad"...`; purchase success: "Yours forever.").

6.1 3D book covers: render each catalog book as a 3D object (CSS `perspective` + `rotateY`, pseudo-element spine using the book's `accent_color`). On hover: spring tilt toward cursor via Framer Motion.
6.2 Animated search results: wrap the results grid in Framer Motion layout animations (`layout` prop + `AnimatePresence`) so books shuffle/enter/exit smoothly as the user types or filters.
6.3 Page transitions: animate Inertia page changes with `AnimatePresence` (fade + slight slide).
6.4 Purchase celebration: on the success page, fire canvas-confetti once and animate the purchased book "flying" into the My Library nav icon.
6.5 Empty states: friendly illustrated/iconographic empty states for empty library, empty search results, empty cart — with playful copy.
6.6 Hard rules: animate only `transform` and `opacity`; respect `prefers-reduced-motion` (disable decorative animations); checkout flow itself stays calm and minimal — celebration happens AFTER payment only.
→ UPDATE PROGRESS.md

## PHASE 7 — POLISH & VERIFICATION

7.1 Run the full test suite; fix all failures.
7.2 Manual verification checklist (record results in PROGRESS.md):
   - Register a customer → buy a book with Stripe test card 4242 4242 4242 4242 → webhook fires (use `stripe listen` locally) → book appears in library → download works.
   - Attempt to access a private file URL directly → must fail.
   - Attempt admin routes as customer → 403.
   - Edit a price as admin → old orders keep their original `order_items.price`.
7.3 Write `README.md`: setup steps, env variables, Stripe webhook setup (`stripe listen --forward-to localhost:8000/stripe/webhook`), php.ini upload limits, seeded admin credentials.
7.4 Final commit. Mark every item in PROGRESS.md complete and write a final CURRENT STATE summary.
→ UPDATE PROGRESS.md

---

## GENERAL RULES

- Work through phases strictly in order. Within a phase, complete steps in order.
- Commit to git after every phase with a descriptive message.
- If anything is ambiguous, choose the simplest option consistent with this prompt, and log the decision in PROGRESS.md under DECISIONS — do not stop to ask.
- Never store e-book binaries in the database. Database stores paths only.
- Never expose `file_path` values in any public API/page response.
- Do not add features not specified here (no reviews, no coupons, no wishlists).
- Remember: after EVERY numbered step — update PROGRESS.md before moving on. This is not optional.
