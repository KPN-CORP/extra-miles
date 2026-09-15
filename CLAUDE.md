# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**Extra Miles** — a Laravel 11 / PHP 8.2 employee-engagement app for KPN Corporation. It bundles two very different frontends in one codebase:

- **Admin back-office** (`/admin/*`): Blade + Bootstrap 5 + jQuery/DataTables, server-rendered, session auth.
- **Employee mobile app** (everything else): a React 19 SPA served from a single Blade shell, talking to `routes/api.php` over JWT.

`docs/new-pic-handover.md` is the project's own handover document and covers conventions and deployment.

## Commands

```bash
composer dev          # serve + queue:listen + pail logs + vite, all concurrently
php artisan serve     # backend only
yarn dev              # vite dev server only (yarn.lock is the committed lockfile)
yarn build            # production asset build -> public/build
./vendor/bin/pint     # PHP formatter — run before committing PHP
php artisan test      # PHPUnit
php artisan test --filter=ExampleTest   # single test / single method
```

`yarn install` pulls the admin theme from `git+https://github.com/coderthemes/admin-resources.git`, so a working GitHub credential (SSH agent or token) has to be in place before `yarn dev` / `yarn build` will run at all. `composer dev` shells out to `npm run dev` for the vite leg even though `yarn.lock` is the committed lockfile.

`tests/Pest.php` exists but Pest is **not** installed — tests run through PHPUnit (`phpunit.xml`). The sqlite in-memory lines in `phpunit.xml` are commented out, so tests hit whatever MySQL databases `.env` points at. Add the sqlite env back before writing DB-touching tests.

Deployment is cPanel-based (`.cpanel.yml`): it copies `app bootstrap config database routes resources` plus a **pre-built** `public/build` to the server and runs `optimize:clear`. Assets are built locally, not on the server — always run `yarn build` before deploying frontend changes.

## Architecture

### Two databases

This is the single most important thing to know. `config/database.php` defines two MySQL connections:

- **`mysql`** (default) — this app's own tables: events, event_participants, surveys, news, quotes, socials, live_contents, form_templates. These are the only tables covered by `database/migrations`.
- **`kpncorp`** — the shared corporate HRIS database, accessed read/write through models that declare `protected $connection = 'kpncorp'`: `User`, `Employee`, `Company`, `Department`, `Designation`, `Grade`, `Location`, `MasterBisnisunit`, `MdcTransaction`, `Role`, `Permission`, `RoleHasPermission`, `ModelHasRole`, `PasswordResetToken`.

Consequences: **never write a migration for a `kpncorp` table** — that schema is owned elsewhere. Eloquent relations that cross the two connections cannot be joined in SQL; existing code loads them separately. Spatie's permission package is pointed at the `kpncorp` `Role`/`Permission` models via `config/permission.php`.

### Authentication (three separate paths)

1. **Admin (session)** — SSO only. `SsoController` receives a base64+XOR-encrypted payload from Darwinbox, verifies the token against `https://kpncorporation.darwinbox.com/checkToken` via cURL, then `Auth::login()`s the matching `User`. There are four entry points (`dbauth`, `dbauthlms`, `dbauthcmpr`, `dbauthexpl`) differing only in the `system` session value and redirect target. `GET /admin/login` just bounces back to Darwinbox — there is no local login form.
2. **Mobile app (JWT)** — `auth.token` middleware (`AuthenticateWithToken`) reads the bearer token, authenticates via `tymon/jwt-auth`, and `Auth::login()`s. Guards `api` (User) and `apiuser` (`ApiUser`, for the `/mdc-transactions` and `/employees` integration endpoints) both use the `jwt` driver.
3. React stores its token in `sessionStorage` (`AuthContext`) and reads the backend base URL from `VITE_API_URL`.

### Routing

`routes/web.php` has a catch-all `GET /{any?}` that returns the `user-app` Blade shell for every path **not** starting with `admin` — this is what makes the React SPA work. Admin routes live inside the `admin` prefix group behind `auth`, `locale`, `notification` middleware, and each feature group is gated by a Spatie permission (`permission:viewmenunews`, `viewmenuevent`, `viewmenusurvey`, `viewmenusocial`, `viewmenulive`, `viewmenuquotes`, `viewmenuform`, `viewroleem`, `viewmenuwellness` with `viewmenuwellnesstype` nested inside it for the master data). New admin features need a matching permission seeded in `RolePermissionSeeder`/`AccessSeeder` — wellness has its own `WellnessPermissionSeeder` — and a `@can(...)` entry in `resources/views/layouts_/shared/left-sidebar.blade.php`, otherwise the pages exist but are unreachable.

Route registration order matters: the SPA catch-all sits above the admin group in the file but is constrained by its `^(?!admin)` regex, and a generic `{first}/{second}` fallback sits at the end of the admin group.

### ID encryption

Public-facing IDs are encrypted, not raw. `Event` (and similar models) expose an `encrypted_id` accessor via `$appends` using `Crypt::encryptString`, and controllers take `{encryptedId}` route params and `Crypt::decryptString` them. When adding a route that exposes a record ID to the browser or the SPA, follow this pattern rather than leaking primary keys. Newer controllers decrypt through the `App\Http\Controllers\Concerns\DecryptsRouteId` trait (`$this->decryptId($encryptedId)`), which turns a tampered id into a 404 instead of a 500 — prefer it over a bare `Crypt::decryptString`.

### Admin views

Two layout trees exist. **`resources/views/layouts_/`** (with the trailing underscore) is the live admin theme — every page under `resources/views/pages/admin/` does `@extends('layouts_.vertical', ['page_title' => '...'])` and fills `@section('css')`, `@section('content')`, `@section('script')`. `resources/views/layouts/app.blade.php` is the older SB-Admin-2 layout still used by a few views. Prefer `layouts_` for new admin pages.

Shared admin partials live in `resources/views/layouts_/shared/`. List pages use client-side DataTables: include `layouts_.shared.admin-datatable-css` in `@section('css')`, give the table `class="js-datatable"`, and the shared initializer in `admin-datatable-js` picks it up (`th.no-sort` disables sorting on a column; it also re-adjusts column widths on Bootstrap tab shows). There is **no** yajra server-side DataTables endpoint in any controller despite the package being installed — all tables render rows in Blade.

### React SPA

Entry `resources/js/users/main.jsx` → `app.jsx`, mounted into `#root` by `resources/views/user-app.blade.php`. `app.jsx` gates the entire app behind a mobile check (`max-width: 768px` **and** touch support) and renders a "Mobile Only" screen on desktop — expect a blank-looking app when testing in a desktop browser without device emulation. Routes are declared in `AnimatedRoutes`; providers are `ApiProvider` (base URL) → `AuthProvider` (token + profile) → `NavigationProvider`.

Imports of the context providers are all spelled `./components/Context/...`, matching the directory on disk — keep it that way, because the lowercase spelling that used to appear here only works on Windows/macOS and breaks a build on a case-sensitive filesystem.

**Page chrome.** Every screen is built from `components/Layout/`: `AppShell` (page background, 480px centred column, safe-area padding, route transition, and — with `nav` — the `BottomNav` tab bar and the bottom padding that clears it), `AppHeader` (sticky top bar with back button, title, and a `trailing` slot), plus `SectionHeader` and `EmptyState`. Tab destinations (`/`, `/event`, `/news`, `/wellness`, `/survey`) render `<AppShell nav>`; drill-down pages get `AppHeader` without the tab bar. Do not hand-roll a back button or a page background — the gradient, the `100dvh` height, `env(safe-area-inset-*)` and the `.tap` press feedback all live in `resources/css/global.css` behind `app-surface` / `app-bg` / `pb-nav` / `tap`. Brand red is the `brand-*` scale in `tailwind.config.js` (`brand-700` is the old `red-700`); older files still say `red-700`.

### Wellness module

The newest and most structured feature; it is the best template for anything added from here on, and it deliberately departs from the older "everything in the controller" style.

Data lives entirely on the default `mysql` connection: `wellness_activity_types` → `wellness_activities` → `wellness_activity_schedules` (one dated session, with `quota` and a `qr_token`) → `wellness_activity_registrations`, plus an append-only `wellness_activity_registration_statuses` audit trail and a module-wide `wellness_blacklists`. All of the string states are backed PHP enums in `app/Enums` (`WellnessActivityStatus`, `WellnessScheduleStatus`, `WellnessRegistrationStatus`, `WellnessRegistrationMethod`, `WellnessRegistrationSource`) and cast on the models — never compare raw strings.

`App\Services\WellnessRegistrationService` owns every state transition (`register`, `confirm`, `cancel`, `requeue`, `blacklist`, `promoteQueue`, `checkIn`). Controllers validate and delegate; they do not mutate a registration's status themselves. The rules that are not obvious from the schema:

- Seats are allocated under a row lock on the schedule. `fifo` confirms while quota remains and queues the rest; `selection` leaves everyone for an admin to confirm.
- Being blacklisted never blocks registration — it only prevents *automatic* confirmation, so an admin has to decide. An admin adding someone counts as that decision and confirms anyway.
- Registrations are never soft-deleted; cancelling is a status transition, and each transition writes a `wellness_activity_registration_statuses` row. Re-registering after cancelling resets `registered_at`, which sends the employee to the back of the FIFO queue.
- Registration rows snapshot the employee's HR fields (unit, job level, location) at registration time, because employees move between units and past reports must not change.
- Attendance is a QR check-in: each schedule generates a `qr_token` UUID on create, admins can rotate it, and the SPA scans it via `WellnessQrScannerModal`.

Surfaces: admin controllers `WellnessActivity/Schedule/Type/Registration/Blacklist` under `/admin/wellness/*` with views in `resources/views/pages/admin/wellness/`; employee endpoints under `/wellness/*` in `Api\WellnessController`; SPA pages `Wellness`, `WellnessDetails`, `MyWellness`.

## Conventions

- PHP: PascalCase classes, camelCase methods/variables, snake_case plural tables. Format with `./vendor/bin/pint`.
- Older features keep their business logic in controllers and the single Livewire component (`ManageParticipants`); `App\Services\AppService` is still an empty stub. The wellness module introduced a real service (`WellnessRegistrationService`) — put multi-step or state-machine logic there rather than growing another fat controller.
- API responses are plain JSON, not a wrapped envelope: `{"message": ..., "token": ...}` on success, `{"error": ..., "messages": {...}}` on failure.
- Uploaded images are served through `GET /images/{filename}` out of `storage/app/public`.
- **Localization (EN / ID).** Both frontends are fully translated and switch independently.
  - **Admin (Blade)** uses Laravel's JSON translations in `resources/lang/{en,id}.json` (note: `resources/lang`, not the Laravel 11 default `lang/` — `Application::langPath()` prefers it when it exists). The English source string *is* the key. `LanguageSwitcher` middleware (alias `locale`) reads `session('locale')`, validating it against `LanguageController::SUPPORTED`, and the `admin/language/{locale}` route (`language.switch`) writes it. The switcher lives in `layouts_/shared/topbar.blade.php`. Wrap every new user-facing string in `__()`; values that reach `__()` through a variable (statuses, tabs, `$link`/`$parentLink` breadcrumbs) still need their possible values present as keys in both files.
  - **Employee app (React)** uses **react-i18next**, configured in `resources/js/users/i18n/` with nested-key dictionaries in `i18n/locales/{en,id}.json`. First-time language is detected from the browser (`navigator.language`), then persisted to `localStorage` under `em_lang`. Components use `useTranslation()`; plain helper modules (`dateTimeHelper`, `wellnessHelper`, `alertHelper`, validation schemas) go through `components/Helper/localeHelper.jsx`, which exposes `translate()` and `localeTag()` for `Intl`/`toLocaleString`. `components/Layout/LanguageToggle.jsx` is the EN/ID switch.
  - Helpers that feed both display and logic (`dateTimeHelper`) return the raw English value *and* a translated label (`eventStatus` / `eventStatusLabel`, `daysUntil` / `daysUntilLabel`) — comparisons stay on the raw value, rendering uses the label.
  - Mail templates (`resources/views/email/`, `auth/reset-email`) are translated but render in whatever locale is active when the mail is built — the scheduler's default, not a per-employee preference. Add a per-user language column if recipient-specific mail language is ever needed.
- Indonesian comments appear throughout the React code.
- Scheduled work is registered in `App\Providers\ScheduleServiceProvider::boot()` (not `routes/console.php`); commands live in `app/Console/Commands`.
- Branch from the active development branch and open a PR against `main`; the repo remote is `KPN-CORP/extra-miles`.
