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

`routes/web.php` has a catch-all `GET /{any?}` that returns the `user-app` Blade shell for every path **not** starting with `admin` — this is what makes the React SPA work. Admin routes live inside the `admin` prefix group behind `auth`, `locale`, `notification` middleware, and each feature group is gated by a Spatie permission (`permission:viewmenunews`, `viewmenuevent`, `viewmenusurvey`, `viewmenusocial`, `viewmenulive`, `viewmenuquotes`, `viewmenuform`, `viewroleem`). New admin features need a matching permission seeded in `RolePermissionSeeder`/`AccessSeeder`.

Route registration order matters: the SPA catch-all sits above the admin group in the file but is constrained by its `^(?!admin)` regex, and a generic `{first}/{second}` fallback sits at the end of the admin group.

### ID encryption

Public-facing IDs are encrypted, not raw. `Event` (and similar models) expose an `encrypted_id` accessor via `$appends` using `Crypt::encryptString`, and controllers take `{encryptedId}` route params and `Crypt::decryptString` them. When adding a route that exposes a record ID to the browser or the SPA, follow this pattern rather than leaking primary keys.

### Admin views

Two layout trees exist. **`resources/views/layouts_/`** (with the trailing underscore) is the live admin theme — every page under `resources/views/pages/admin/` does `@extends('layouts_.vertical', ['page_title' => '...'])` and fills `@section('css')`, `@section('content')`, `@section('script')`. `resources/views/layouts/app.blade.php` is the older SB-Admin-2 layout still used by a few views. Prefer `layouts_` for new admin pages.

Shared admin partials live in `resources/views/layouts_/shared/`. List pages use client-side DataTables: include `layouts_.shared.admin-datatable-css` in `@section('css')`, give the table `class="js-datatable"`, and the shared initializer in `admin-datatable-js` picks it up (`th.no-sort` disables sorting on a column; it also re-adjusts column widths on Bootstrap tab shows). There is **no** yajra server-side DataTables endpoint in any controller despite the package being installed — all tables render rows in Blade.

### React SPA

Entry `resources/js/users/main.jsx` → `app.jsx`, mounted into `#root` by `resources/views/user-app.blade.php`. `app.jsx` gates the entire app behind a mobile check (`max-width: 768px` **and** touch support) and renders a "Mobile Only" screen on desktop — expect a blank-looking app when testing in a desktop browser without device emulation. Routes are declared in `AnimatedRoutes`; providers are `ApiProvider` (base URL) → `AuthProvider` (token + profile) → `NavigationProvider`.

Note that imports mix `./components/context/...` and `./components/Context/...`; the directory on disk is `Context`. This survives on Windows/macOS but is a case-sensitivity hazard if a build ever runs on Linux.

## Conventions

- PHP: PascalCase classes, camelCase methods/variables, snake_case plural tables. Format with `./vendor/bin/pint`.
- Business logic lives in controllers and the single Livewire component (`ManageParticipants`); `App\Services\AppService` is an empty stub — there is no service/repository layer to fit into.
- API responses are plain JSON, not a wrapped envelope: `{"message": ..., "token": ...}` on success, `{"error": ..., "messages": {...}}` on failure.
- Uploaded images are served through `GET /images/{filename}` out of `storage/app/public`.
- Admin UI locale switches between `lang/en.json` and `lang/id.json` via the `locale` middleware and a session value; Indonesian comments appear throughout the React code.
- Scheduled work is registered in `App\Providers\ScheduleServiceProvider::boot()` (not `routes/console.php`); commands live in `app/Console/Commands`.
- Branch from the active development branch and open a PR against `main`; the repo remote is `KPN-CORP/extra-miles`.
