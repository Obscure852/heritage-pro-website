# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Path

This project is located at:

```
/Users/thatoobuseng/Sites/Heritage Website
```

It is a **separate project** from the Heritage Junior school management system at `/Users/thatoobuseng/Sites/Junior`. They share a product name and nothing else — no shared models, routes, or views. Do not modify files outside this project path.

## Project Overview

A Laravel 9.x (PHP 8.0+) application serving two distinct halves from one codebase:

1. **The public Heritage Pro marketing website** — 8 pages, a journal (index plus article pages), and a demo-request form. Handled entirely by one controller, `PublicWebsiteController`, with content driven from `config/heritage_website.php` and `config/heritage_journal.php`.
2. **The internal Heritage Pro CRM** — the bulk of the code. Customers, leads, contacts, quotes/invoices, calendar, discussions (in-app, email, WhatsApp), staff attendance (including biometric devices), leave management, imports, and settings.

Heritage Pro itself — the school information system being sold — is a different product in a different repository. Nothing here manages learners, grades, or report cards; those appear only as marketing copy and mock UI on the website.

## Common Commands

```bash
php artisan serve                     # Development server
php artisan test                      # Full suite (36 test files)
php artisan test --filter=TestName    # Single test or class
php artisan migrate                   # Run migrations
php artisan tinker
```

Cache clearing — `view:clear` matters most here, because nearly all CSS lives in compiled Blade templates:

```bash
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

**Commands that do not work in this repo:**
- `php artisan migrate:fresh --seed` — there is no `database/seeders` or `database/factories` directory, despite `composer.json` autoloading the namespace. Use `migrate:fresh` alone.
- `npm run dev` / `npm run build` — see *Asset pipeline* below. There is no build step; do not add one casually.

## Architecture

### Routing

`routes/web.php` is the whole map and worth reading first. Three zones:

- **Public website** — a single `Route::controller(PublicWebsiteController::class)` group. `/` plus seven pages resolved through a shared `page` action with a `defaults('page', …)` allowlist, `/journal` and `/journal/{slug}`, and `POST /book-demo`.
- **Signed public link** — `crm.calendar.attendees.availability`, the only CRM route reachable without a login (guarded by `signed`).
- **CRM** — everything under `/crm`, behind `auth` + `crm.access`, then `crm.onboarding`. Split across 15 files in `routes/crm/` (`customers`, `contacts`, `calendar`, `products`, `requests`, `discussions`, `attendance`, `leave`, `users`, `settings`, `integrations`, `workspace`, `dashboard`, `onboarding`, `dev`).

`routes/api.php` exposes biometric attendance endpoints. The ZKTeco ADMS `iclock/*` routes are deliberately outside Sanctum — devices authenticate by serial number plus communication key.

### Module permissions (the central CRM pattern)

CRM authorization is **config-driven, not policy-driven**. `config/heritage_crm.php` (~820 lines) declares every module with its label, icon, route, route-match patterns, and `default_permissions` per role (`admin`, `finance`, `manager`, `rep`).

`EnsureCrmAccess` middleware resolves the current route name to a module via `CrmModulePermissionService`, works out the required level (`view` / `edit` / `admin`) for the HTTP method, and aborts 403 if the user falls short. Per-user overrides live in `crm_user_module_permissions`; absent an override, the role default applies.

**Consequence:** adding a CRM route without registering its name in a module's `match` patterns leaves it unguarded by module permissions. Register new routes in `config/heritage_crm.php`, and check `CrmAccessTest` still passes.

### Layers

- **Controllers** — `app/Http/Controllers/Crm/` (34 files), plus `Api/Crm/BiometricController` and the single `PublicWebsiteController`.
- **Services** — `app/Services/Crm/` (37 files) holds the real business logic: attendance clocking/grid/shift resolution, biometric event processing, commercial document calculation/numbering/PDF/sharing, leave application/approval/balance, discussion delivery, module permissions, calendar, global search.
- **Models** — 59 in `app/Models/`, nearly all `Crm`-prefixed. The unprefixed ones (`Lead`, `Customer`, `Contact`, `SalesStage`, `Request`-family, `Discussion`-family, `Integration`, `User`) predate the prefix convention.
- **Form Requests** — `app/Http/Requests/Crm/` plus `BookDemoRequest`.
- **Jobs** — attendance automation (`MarkAbsenteesJob`, `CloseOvernightRecordsJob`, `ProcessBiometricEventJob`, `SyncHolidayAttendanceJob`).
- **Console commands** — leave reminders/escalation/balance reset, calendar reminders. Scheduled in `app/Console/Kernel.php`.

### Views

- `resources/views/crm/` — one directory per module, extending `layouts/crm`.
- `resources/views/website/` — `pages/`, `sections/`, `partials/`, `partials/editorial/`.
- `resources/views/pdf/crm/` — DomPDF templates for quotes and invoices.
- `resources/views/layouts/` — the CRM shell is split across `crm.blade.php`, `crm-head-css`, `crm-sidebar`, `crm-topbar`, `crm-styles`, `crm-theme-styles`, `crm-footer`.

### Asset pipeline

**There is no active build step.** `@vite` appears nowhere in the codebase. Understand this before touching styling:

- The CRM loads pre-built vendor CSS/JS as static files from `public/assets/` (a Minia Bootstrap 5 admin template) via `layouts/crm-head-css`.
- All bespoke CSS lives **inside Blade files**, inlined through `@include` into a `<style>` tag — `layouts/crm-styles` (766 lines), `layouts/crm-theme-styles` (2,238 lines), `layouts/website-base-styles`, and `website/partials/editorial/*-styles`.
- `package.json` and `vite.config.js` are inherited from the template and are effectively dead. Editing `resources/css/` or `resources/js/` changes nothing that ships.

So: add styles to the relevant Blade style partial and run `php artisan view:clear`. Icons are Boxicons (`bx bx-*`), loaded from `public/assets/css/icons.min.css`.

### Key packages

`composer.json` declares only the Laravel base. In practice the app also depends on **`barryvdh/laravel-dompdf`** (commercial document PDFs) and **`maatwebsite/excel`** (attendance exports, CRM imports) — both present in `composer.lock` and `vendor/`, neither declared in `composer.json`.

`composer validate` reports the lock file as out of sync. **Do not run `composer update`** — it would drop DomPDF and Excel and break PDF generation and imports. If dependencies need touching, add those two packages to `composer.json` first.

## Public Website Specifics

Content lives in `config/heritage_website.php` (~524 lines): nav, per-page hero copy, clients, stats, products, feature rows, modules, deployment highlights, customer cards, team, pricing cards, FAQ, blog articles, contact details, footer columns. Prefer editing config over hardcoding copy into partials.

### The journal

`config/heritage_journal.php` holds the full text of every article. Bodies are **block arrays**, not HTML — `heading`, `paragraph`, `list` (optionally `ordered`) and `pull` — rendered by `editorial/article-body`, so markup stays in the template. Articles are newest-first: the homepage shows the first three, `/journal` lists them all, `/journal/{slug}` renders one and 404s on an unknown slug. Add an article by appending an entry with a unique `slug`; no route or view changes are needed. `PublicWebsiteJournalTest` asserts every block of every article reaches the page.

### The demo form

`POST /book-demo` validates through `BookDemoRequest` (`full_name`, `role`, `institution`, `work_email`, `phone`, `edition`, `learner_band`, optional `notes`), mails `BookDemoInquiry` to `config('mail.demo_recipient')`, and redirects back to `#contact` with `book_demo_success` / `book_demo_error` in the session. Any redesign of the form must keep all eight field names and that anchor.

### Two design systems coexist

| | Homepage (`/`) | The other 7 pages |
|---|---|---|
| Layout | `layouts/website-editorial` | `layouts/website-master` |
| Partials | `website/partials/editorial/` | `website/partials/` |
| Class prefix | `hp-` | legacy (`nav`, `hero`, `contact`, …) |
| Type | Source Serif 4 / Archivo / IBM Plex Mono | Inter-based |
| Palette | cream `#FAFAFD`, navy `#232160`, gold `#C08A3C` | indigo `--brand-indigo-500` |
| Dark mode | none | `data-theme` toggle + `localStorage` |

This is deliberate: the homepage was rebuilt to a supplied editorial design and the rollout was scoped to that page. Do not "unify" them without being asked. The `precision-*` and `mobile-app-showcase` partials are orphaned remnants of the previous homepage — left on disk, referenced by nothing.

Two content items are flagged in Blade comments and still need the owner's decision: the attributed testimonials in `editorial/testimonials.blade.php`, and per-year vs per-month pricing in `editorial/pricing.blade.php` (which contradicts `config/heritage_website.php`).

## Testing

PHPUnit against **SQLite** (`database/testing.sqlite`, configured in `phpunit.xml`), mail faked to the `array` driver. 36 test files, overwhelmingly `tests/Feature/Crm/`.

There are no model factories. Tests build fixtures by hand — follow the pattern in the neighbouring test rather than reaching for `factory()`.

Ignore `tests/Concerns/` — its seven traits (`EnsuresPdpPhaseTwoSchema`, `EnsuresInvigilationSchema`, `EnsuresPreF3SchoolModeSchema`, `BuildsActivitiesRosterFixtures`, …) are dead copies from the Junior school-SIS repo, referenced by zero tests here and describing tables this project does not have.

When touching CRM routes or permissions, `CrmAccessTest` and `CrmPageRenderTest` are the fastest signal.

## Coding Standards & Best Practices

### Code Style & Formatting
- **Brace Style:** Use "Same Line" (K&R / 1TBS) braces for all methods, classes, and control structures.
  - *Correct:* `public function index() {`
  - *Incorrect:* `public function index()` followed by `{` on a new line.
- **Type Hinting:** Strictly use return types and typed properties where possible (e.g., `public function grade(Student $student): float {`).
- **Naming:** Follow Laravel naming conventions (camelCase for variables/methods, snake_case for database columns).
- **Constructor injection:** Services are resolved through the container with `private readonly` promoted properties — see `EnsureCrmAccess` and the `Crm` controllers.

### Performance & Optimization
- **Database Queries:**
  - **No N+1 Queries:** Always use Eager Loading (`with()`) for relationships in loops.
  - **Select Specific Columns:** Avoid `select *`. Fetch only needed columns (e.g., `User::select('id', 'name')->get()`) to reduce memory usage.
  - **Indexing:** Ensure foreign keys and frequently searched columns are indexed in migrations.
- **Caching:** Cache expensive dashboard queries or static reference data using `Cache::remember`.

### Robustness & Security
- **Validation:** Never use `$request->all()`. Use FormRequests or `validated()` data for all state-changing operations.
- **Authorization:** CRM access is enforced by module permissions (see above), not policies — register new routes in `config/heritage_crm.php`.
- **Race Conditions:**
  - **Transactions:** Wrap all multi-step database updates in `DB::transaction(function() { ... })`.
  - **Locking:** Use `lockForUpdate()` when reading data that will be immediately modified to prevent race conditions.
- **Error Handling:** Use `try/catch` blocks for external service calls or file operations. Log errors using `Log::error()` with context.

### Frontend Considerations
- **Asset Loading:** Defer non-critical JavaScript.
- **Feedback:** Always provide user feedback on success or failure of async operations.

## UI Conventions

### CRM pages

CRM views extend `layouts/crm` and populate named sections rather than building their own chrome:

```blade
@extends('layouts.crm')

@section('title', 'Customers Workspace - Customers')
@section('crm_heading', 'Customers Workspace')
@section('crm_subheading', 'Manage converted institutions and keep records tied to their originating lead.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $count, 'label' => 'Active'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Customer Directory',
            'content' => 'Use the filters below to find the account you need.',
        ])

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find customers</h2>
                </div>
            </div>
            {{-- … --}}
        </section>
    </div>
@endsection
```

Established class vocabulary, defined in `layouts/crm-styles` and `layouts/crm-theme-styles` — reuse these rather than inventing new ones: `crm-stack`, `crm-card`, `crm-card-title`, `crm-kicker`, `crm-field`, `crm-field-grid`, `crm-field-error`, `crm-form`, `crm-filter-form`, `crm-filter-grid`, `crm-table` / `crm-table-wrap` / `crm-table-actions`, `crm-pill`, `crm-metric`, `crm-meta-row`, `crm-action-row`, `crm-empty`, `crm-muted`, `crm-inline`, `crm-tabs`.

**Buttons.** Primary actions use `btn btn-primary`; secondary and cancel actions use `btn btn-light crm-btn-light`; table row icons use `btn crm-icon-action` (with `crm-icon-danger` for destructive ones). Reusable partials exist for `crm.partials.view-button`, `crm.partials.delete-button`, and `crm.partials.pager`.

**Save buttons must carry the loading pattern** (~56 instances across the CRM):

```blade
<button type="submit" class="btn btn-primary btn-loading">
    <span class="btn-text"><i class="bx bx-save"></i> Save Changes</span>
    <span class="btn-spinner d-none">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Saving...
    </span>
</button>
```

**Feedback.** Do not hand-roll alerts. Flash `crm_success` or `crm_error` to the session; `crm.partials.flash` renders the toast stack, and also surfaces `$errors` automatically.

### Public website

The homepage uses `hp-`-prefixed classes with tokens declared in `website/partials/editorial/base-styles`; section styles go in `editorial/home-styles`. The remaining pages use the legacy vocabulary in `layouts/website-base-styles` (`--brand-indigo-500`, `--fg-1/2/3`, `--border-1`, `--shadow-sm`) and must keep working in both light and dark themes.
